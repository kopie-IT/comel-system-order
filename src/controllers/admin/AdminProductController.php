<?php

class AdminProductController {
    public function index(): void {
        $search     = trim($_GET['search'] ?? '');
        $categoryId = (int)($_GET['category_id'] ?? 0);
        $products   = (new Product())->all($categoryId, $search);
        $categories = (new Category())->all();
        view('admin/products/index', [
            'products'   => $products,
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
                'categories' => $categories,
                'errors'     => $errors,
                'old'        => $_POST,
            ], 'admin');
            return;
        }

        $imagePath  = $this->handleImageUpload($_FILES['image'] ?? null);
        $productId  = (new Product())->create([
            'category_id' => (int)$_POST['category_id'],
            'name'        => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'image'       => $imagePath,
            'price'       => (float)($_POST['price'] ?? 0),
            'stock'       => (int)($_POST['stock'] ?? 0),
        ]);

        $this->saveSizes($productId, $_POST);

        flash('success', 'Produk berjaya ditambah.');
        redirect('/admin/products');
    }

    public function edit(int $id): void {
        $product = (new Product())->find($id);
        if (!$product) { redirect('/admin/products'); return; }
        $sizes      = (new ProductSize())->forProduct($id);
        $categories = (new Category())->all();
        view('admin/products/form', [
            'product'    => $product,
            'sizes'      => $sizes,
            'categories' => $categories,
            'errors'     => [],
        ], 'admin');
    }

    public function update(int $id): void {
        csrf_verify();
        $product = (new Product())->find($id);
        if (!$product) { redirect('/admin/products'); return; }

        $errors = $this->validate($_POST, $_FILES, true);
        if (!empty($errors)) {
            $sizes      = (new ProductSize())->forProduct($id);
            $categories = (new Category())->all();
            view('admin/products/form', [
                'product'    => $product,
                'sizes'      => $sizes,
                'categories' => $categories,
                'errors'     => $errors,
                'old'        => $_POST,
            ], 'admin');
            return;
        }

        $productModel = new Product();
        $productModel->update($id, [
            'category_id' => (int)$_POST['category_id'],
            'name'        => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'price'       => (float)($_POST['price'] ?? 0),
            'stock'       => (int)($_POST['stock'] ?? 0),
        ]);

        if (!empty($_FILES['image']['name'])) {
            // Delete old image
            if ($product['image'] && file_exists(ROOT_PATH . '/public' . $product['image'])) {
                unlink(ROOT_PATH . '/public' . $product['image']);
            }
            $imagePath = $this->handleImageUpload($_FILES['image']);
            $productModel->updateImage($id, $imagePath);
        }

        (new ProductSize())->deleteForProduct($id);
        $this->saveSizes($id, $_POST);

        flash('success', 'Produk berjaya dikemaskini.');
        redirect('/admin/products');
    }

    public function destroy(int $id): void {
        csrf_verify();
        $product = (new Product())->find($id);
        if (!$product) { redirect('/admin/products'); return; }

        if ((new Product())->isInOrders($id)) {
            flash('error', 'Produk tidak boleh dipadam kerana terdapat dalam pesanan.');
            redirect('/admin/products');
            return;
        }

        if ($product['image'] && file_exists(ROOT_PATH . '/public' . $product['image'])) {
            unlink(ROOT_PATH . '/public' . $product['image']);
        }

        (new ProductSize())->deleteForProduct($id);
        (new Product())->delete($id);

        flash('success', 'Produk berjaya dipadam.');
        redirect('/admin/products');
    }

    private function validate(array $post, array $files, bool $isEdit = false): array {
        $errors = [];
        if (trim($post['name'] ?? '') === '') { $errors[] = 'Nama produk diperlukan.'; }
        if (empty($post['category_id']))       { $errors[] = 'Kategori diperlukan.'; }
        if (!$isEdit && empty($files['image']['name'])) { $errors[] = 'Gambar produk diperlukan.'; }
        if (!empty($files['image']['name'])) {
            $allowed  = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($files['image']['tmp_name']);
            if (!in_array($mimeType, $allowed)) { $errors[] = 'Format gambar tidak disokong.'; }
            if ($files['image']['size'] > 2 * 1024 * 1024) { $errors[] = 'Saiz gambar terlalu besar (maks 2MB).'; }
        }
        return $errors;
    }

    private function handleImageUpload(?array $file): ?string {
        if (!$file || empty($file['name'])) return null;
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
        $dir      = ROOT_PATH . '/public/uploads/products/';
        if (!is_dir($dir)) { mkdir($dir, 0755, true); }
        move_uploaded_file($file['tmp_name'], $dir . $filename);
        return '/uploads/products/' . $filename;
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
