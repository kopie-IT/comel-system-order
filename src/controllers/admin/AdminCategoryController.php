<?php

class AdminCategoryController {
    public function index(): void {
        $categories = (new Category())->withProductCount();
        view('admin/categories/index', ['categories' => $categories], 'admin');
    }

    public function create(): void {
        view('admin/categories/form', ['category' => null, 'errors' => []], 'admin');
    }

    public function store(): void {
        csrf_verify();
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'produk';

        $errors = $this->validate($name, $type);
        if (!empty($errors)) {
            view('admin/categories/form', ['category' => null, 'errors' => $errors, 'old' => $_POST], 'admin');
            return;
        }

        (new Category())->create($name, $type);
        flash('success', 'Kategori berjaya ditambah.');
        redirect('/admin/categories');
    }

    public function edit(int $id): void {
        $category = (new Category())->find($id);
        if (!$category) { redirect('/admin/categories'); return; }
        view('admin/categories/form', ['category' => $category, 'errors' => []], 'admin');
    }

    public function update(int $id): void {
        csrf_verify();
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'produk';

        $errors = $this->validate($name, $type, $id);
        if (!empty($errors)) {
            $category = (new Category())->find($id);
            view('admin/categories/form', ['category' => $category, 'errors' => $errors, 'old' => $_POST], 'admin');
            return;
        }

        (new Category())->update($id, $name, $type);
        flash('success', 'Kategori berjaya dikemaskini.');
        redirect('/admin/categories');
    }

    public function destroy(int $id): void {
        csrf_verify();
        $model = new Category();
        if ($model->hasProducts($id)) {
            flash('error', 'Kategori tidak boleh dipadam kerana mempunyai produk.');
            redirect('/admin/categories');
            return;
        }
        $model->delete($id);
        flash('success', 'Kategori berjaya dipadam.');
        redirect('/admin/categories');
    }

    private function validate(string $name, string $type, int $excludeId = 0): array {
        $errors = [];
        if ($name === '') { $errors[] = 'Nama kategori diperlukan.'; }
        if (!in_array($type, ['pakaian', 'produk'])) { $errors[] = 'Jenis kategori tidak sah.'; }
        if ($name !== '' && (new Category())->nameExists($name, $excludeId)) {
            $errors[] = 'Nama kategori sudah wujud.';
        }
        return $errors;
    }
}
