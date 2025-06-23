<?php

namespace MyAwesomeWebsite;

class ProductController extends Controller
{
    private ProductRepository $productRepository;

    public function __construct()
    {
        $this->productRepository = new ProductRepository();
        parent::__construct();
    }

    public function productsPage()
    {
        $template = 'products.html.twig';
        $products = $this->productRepository->findAll();
        $args = [
            'products' => $products
        ];
        print $this->twig->render($template, $args);
    }

    public function productDetailPage($id)
    {
        $template = 'product-detail.html.twig';
        $product = $this->productRepository->find($id);
        $args = [
            'product' => $product
        ];

        print $this->twig->render($template, $args);
    }

}

?>
