<?php

class CategoryController {
    public function show(int $id): void {
        $category = (new Category())->find($id);
        if (!$category) {
            http_response_code(404);
            view('errors/404', [], 'public');
            return;
        }
        $products = (new Product())->all($id);
        view('category/show', [
            'category' => $category,
            'products' => $products,
        ], 'public');
    }
}
