<?php

class CategoryController {
    public function show(int $id): void {
        $category = (new Category())->find($id);
        if (!$category) {
            http_response_code(404);
            view('errors/404', [], 'public');
            return;
        }
        $productModel = new Product();
        $products     = $productModel->all($id, '', true);
        $productIds   = array_column($products, 'id');
        $extraImages  = $productModel->getImagesForProducts($productIds);
        foreach ($products as &$product) {
            $imgs              = $extraImages[$product['id']] ?? [];
            $product['images'] = !empty($imgs) ? $imgs : ($product['image'] ? [$product['image']] : []);
        }
        unset($product);
        view('category/show', [
            'category' => $category,
            'products' => $products,
        ], 'public');
    }
}
