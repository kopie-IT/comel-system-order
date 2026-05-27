<?php

class HomeController {
    public function index(): void {
        $productModel = new Product();
        $categories   = (new Category())->all();
        foreach ($categories as &$cat) {
            $cat['preview_images'] = $productModel->getPreviewImagesForCategory($cat['id']);
        }
        unset($cat);
        view('home/index', ['categories' => $categories], 'public');
    }
}

