<?php

class ProductController {
    public function show(int $id): void {
        $product = (new Product())->find($id);
        if (!$product) {
            http_response_code(404);
            view('errors/404', [], 'public');
            return;
        }
        $sizes        = [];
        $variants     = (new ProductVariant())->forProduct($id);
        $productModel = new Product();
        $imgRows      = $productModel->getImages($id);
        $images       = !empty($imgRows)
            ? array_column($imgRows, 'image')
            : ($product['image'] ? [$product['image']] : []);
        if ($product['category_type'] === 'pakaian') {
            $sizes = (new ProductSize())->forProduct($id);
        }
        view('product/show', [
            'product'  => $product,
            'sizes'    => $sizes,
            'images'   => $images,
            'variants' => $variants,
        ], 'public');
    }
}
