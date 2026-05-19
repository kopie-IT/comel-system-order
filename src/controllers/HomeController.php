<?php

class HomeController {
    public function index(): void {
        $categories = (new Category())->all();
        view('home/index', ['categories' => $categories], 'public');
    }
}
