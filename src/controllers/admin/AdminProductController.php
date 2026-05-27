<?php

class AdminProductController {
    public function index(): void {
        $search     = trim($_GET['search'] ?? '');
        $categoryId = (int)($_GET['category_id'] ?? 0);
        $products   = (new Product())->all($categoryId, $search);
        $trashed    = (new Product())->trashed();
        $categories = (new Category())->all();
        view('admin/products/index', [
            'products'   => $products,
            'trashed'    => $trashed,
            'categories' => $categories,
            'search'     => $search,
            'categoryId' => $categoryId,
        ], 'admin');
    }

    public function create(): void {
        $categories = (new Category())->all();
        view('admin/products/form', [
            'product'    => null,
            'sizes'      => [],
            'variants'   => [],
            'images'     => [],
            'categories' => $categories,
            'errors'     => [],
        ], 'admin');
    }

    public function store(): void {
        csrf_verify();
        $errors = $this->validate($_POST, $_FILES);
        if (!empty($errors)) {
            $categories = (new Category())->all();
            view('admin/products/form', [
                'product'    => null,
                'sizes'      => [],
                'images'     => [],
                'categories' => $categories,
                'errors'     => $errors,
                'old'        => $_POST,
            ], 'admin');
            return;
        }

        $imagePaths   = $this->handleMultipleImageUpload($_FILES['images'] ?? null);
        $primaryImage = $imagePaths[0] ?? null;

        $productModel = new Product();
        $productId    = $productModel->create([
            'category_id' => (int)$_POST['category_id'],
            'name'        => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'image'       => $primaryImage,
            'price'       => (float)($_POST['price'] ?? 0),
            'stock'       => (int)($_POST['stock'] ?? 0),
        ]);

        foreach ($imagePaths as $i => $path) {
            $productModel->addImage($productId, $path, $i);
        }

        $this->saveSizes($productId, $_POST);
        $this->saveVariants($productId, $_POST, $_FILES);

        flash('success', 'Produk berjaya ditambah.');
        redirect('/admin/products');
    }

    public function edit(int $id): void {
        $product = (new Product())->find($id);
        if (!$product) { redirect('/admin/products'); return; }
        $sizes      = (new ProductSize())->forProduct($id);
        $variants   = (new ProductVariant())->forProduct($id);
        $categories = (new Category())->all();
        $images     = (new Product())->getImages($id);
        view('admin/products/form', [
            'product'    => $product,
            'sizes'      => $sizes,
            'variants'   => $variants,
            'images'     => $images,
            'categories' => $categories,
            'errors'     => [],
        ], 'admin');
    }

    public function update(int $id): void {
        csrf_verify();
        $productModel = new Product();
        $product      = $productModel->find($id);
        if (!$product) { redirect('/admin/products'); return; }

        $errors = $this->validate($_POST, $_FILES, true);
        if (!empty($errors)) {
            $sizes      = (new ProductSize())->forProduct($id);
            $categories = (new Category())->all();
            $images     = $productModel->getImages($id);
            view('admin/products/form', [
                'product'    => $product,
                'sizes'      => $sizes,
                'images'     => $images,
                'categories' => $categories,
                'errors'     => $errors,
                'old'        => $_POST,
            ], 'admin');
            return;
        }

        $productModel->update($id, [
            'category_id' => (int)$_POST['category_id'],
            'name'        => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'price'       => (float)($_POST['price'] ?? 0),
            'stock'       => (int)($_POST['stock'] ?? 0),
        ]);

        // Delete individually marked images
        $deleteIds = array_map('intval', $_POST['delete_image'] ?? []);
        foreach ($deleteIds as $imgId) {
            $path = $productModel->deleteImage($imgId);
            if ($path && file_exists(ROOT_PATH . '/public' . $path)) {
                unlink(ROOT_PATH . '/public' . $path);
            }
        }

        // Upload new images
        if (!empty($_FILES['images']['name'][0])) {
            $newPaths  = $this->handleMultipleImageUpload($_FILES['images']);
            $existing  = $productModel->getImages($id);
            $nextOrder = count($existing);
            foreach ($newPaths as $i => $path) {
                $productModel->addImage($id, $path, $nextOrder + $i);
            }
        }

        // Sync primary image to first in product_images
        $allImages = $productModel->getImages($id);
        $productModel->updateImage($id, !empty($allImages) ? $allImages[0]['image'] : null);

        (new ProductSize())->deleteForProduct($id);
        $this->saveSizes($id, $_POST);

        // Delete old variant images from disk before re-saving
        $oldVariants = (new ProductVariant())->forProduct($id);
        foreach ($oldVariants as $ov) {
            if (!empty($ov['image']) && file_exists(ROOT_PATH . '/public' . $ov['image'])) {
                unlink(ROOT_PATH . '/public' . $ov['image']);
            }
        }
        (new ProductVariant())->deleteForProduct($id);
        $this->saveVariants($id, $_POST, $_FILES);

        flash('success', 'Produk berjaya dikemaskini.');
        redirect('/admin/products');
    }

    public function destroy(int $id): void {
        csrf_verify();
        $product = (new Product())->find($id);
        if (!$product) { redirect('/admin/products'); return; }

        (new Product())->softDelete($id);

        flash('success', 'Produk berjaya dilumpuhkan.');
        redirect('/admin/products');
    }

    public function restore(int $id): void {
        csrf_verify();
        (new Product())->restore($id);
        flash('success', 'Produk berjaya dipulihkan.');
        redirect('/admin/products');
    }

    private function validate(array $post, array $files, bool $isEdit = false): array {
        $errors = [];
        if (trim($post['name'] ?? '') === '') { $errors[] = 'Nama produk diperlukan.'; }
        if (empty($post['category_id']))       { $errors[] = 'Kategori diperlukan.'; }
        if (!$isEdit && empty($files['images']['name'][0])) { $errors[] = 'Gambar produk diperlukan.'; }
        if (!empty($files['images']['name'][0])) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo   = new finfo(FILEINFO_MIME_TYPE);
            foreach ($files['images']['tmp_name'] as $i => $tmpName) {
                if (empty($files['images']['name'][$i])) continue;
                $mimeType = $finfo->file($tmpName);
                if (!in_array($mimeType, $allowed)) {
                    $errors[] = 'Format gambar tidak disokong (gambar ' . ($i + 1) . ').';
                }
                if ($files['images']['size'][$i] > 2 * 1024 * 1024) {
                    $errors[] = 'Saiz gambar terlalu besar - maks 2MB (gambar ' . ($i + 1) . ').';
                }
            }
        }
        return $errors;
    }

    private function handleMultipleImageUpload(?array $files): array {
        if (!$files || empty($files['name'][0])) return [];
        $paths = [];
        $dir   = ROOT_PATH . '/public/uploads/products/';
        if (!is_dir($dir)) { mkdir($dir, 0755, true); }
        foreach ($files['tmp_name'] as $i => $tmpName) {
            if (empty($files['name'][$i])) continue;
            $ext      = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
            $filename = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
            move_uploaded_file($tmpName, $dir . $filename);
            $paths[] = '/uploads/products/' . $filename;
        }
        return $paths;
    }

    private function saveVariants(int $productId, array $post, array $files = []): void {
        $labels = $post['variant_label'] ?? [];
        $prices = $post['variant_price'] ?? [];
        $stocks = $post['variant_stock'] ?? [];
        if (!is_array($labels)) return;

        $variantModel = new ProductVariant();
        $dir          = ROOT_PATH . '/public/uploads/products/';
        if (!is_dir($dir)) { mkdir($dir, 0755, true); }

        // Normalise the variant_image file array into per-index entries
        $variantImages = [];
        if (!empty($files['variant_image']['tmp_name'])) {
            foreach ($files['variant_image']['tmp_name'] as $i => $tmpName) {
                $variantImages[$i] = [
                    'tmp_name' => $tmpName,
                    'name'     => $files['variant_image']['name'][$i] ?? '',
                    'size'     => $files['variant_image']['size'][$i] ?? 0,
                    'error'    => $files['variant_image']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                ];
            }
        }

        foreach ($labels as $i => $label) {
            $label = trim($label);
            if ($label === '') continue;

            $imagePath = null;
            if (!empty($variantImages[$i]['tmp_name'])
                && $variantImages[$i]['error'] === UPLOAD_ERR_OK
                && !empty($variantImages[$i]['name'])
            ) {
                $ext       = strtolower(pathinfo($variantImages[$i]['name'], PATHINFO_EXTENSION));
                $filename  = 'variant_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                move_uploaded_file($variantImages[$i]['tmp_name'], $dir . $filename);
                $imagePath = '/uploads/products/' . $filename;
            }

            $variantModel->create($productId, $label, (float)($prices[$i] ?? 0), (int)($stocks[$i] ?? 0), $imagePath);
        }
    }

    private function saveSizes(int $productId, array $post): void {
        $labels = $post['size_label'] ?? [];
        $prices = $post['size_price'] ?? [];
        $stocks = $post['size_stock'] ?? [];
        if (!is_array($labels)) return;
        $sizeModel = new ProductSize();
        foreach ($labels as $i => $label) {
            $label = trim($label);
            if ($label === '') continue;
            $sizeModel->create(
                $productId,
                $label,
                (float)($prices[$i] ?? 0),
                (int)($stocks[$i] ?? 0)
            );
        }
    }
}
