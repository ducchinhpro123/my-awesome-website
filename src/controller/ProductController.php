<?php

namespace MyAwesomeWebsite\controller;

use MyAwesomeWebsite\repository\ProductRepository;
use MyAwesomeWebsite\Controller;

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
        $page = filter_input(INPUT_GET, "page", FILTER_VALIDATE_INT);

        if ($page === false || $page < 0) {
            $page = 0;
        }

        $totalItems = $this->productRepository->getTotalProductsNumber();
        $itemsPerPage = $this->productRepository::ITEM_PER_PAGE;
        $totalPages = ceil($totalItems / $itemsPerPage);
        $startNumberItemDisplay = 1;
        $endNumberItemDisplay = $itemsPerPage * ($page + 1);

        if ($endNumberItemDisplay > $totalItems) {
            $endNumberItemDisplay = $totalItems;
            $startNumberItemDisplay = $endNumberItemDisplay;
        } else if ($endNumberItemDisplay > $itemsPerPage) {
            $startNumberItemDisplay = $endNumberItemDisplay - $itemsPerPage;
        }

        $productsPagination = $this->productRepository->getProductsPagination($page);
        $this->args['products'] = $productsPagination;

        $this->args['pageNumber'] = $page + 1;
        $this->args['totalPages'] = $totalPages;
        $this->args['totalItemsNumber'] = $totalItems;
        $this->args['pagination'] = [
            'current_page' => $page,
            'has_previous' => $page + 1 > 1,
            'has_next' => $page + 1 < $totalPages,
            'totalPages' => $totalPages,
            'totalItemPerPage' => $itemsPerPage,
            'endNumberItemDisplay' => $endNumberItemDisplay,
            'startNumberItemDisplay' => $startNumberItemDisplay
        ];

        print $this->twig->render($template, $this->args);
    }

    public function productDetailPage($id)
    {
        $template = 'product-detail.html.twig';
        $product = $this->productRepository->find($id);

        $this->args['product'] = $product;

        print $this->twig->render($template, $this->args);
    }

}

?>
