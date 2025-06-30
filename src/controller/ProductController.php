<?php

namespace MyAwesomeWebsite\controller;

use Doctrine\ORM\Tools\Pagination\Paginator;

use MyAwesomeWebsite\repository\ProductRepository;
use MyAwesomeWebsite\Controller;
use MyAwesomeWebsite\model\Product;
use MyAwesomeWebsite\repository\CategoryRepository;

class ProductController extends Controller
{
    private ProductRepository $productRepository;
    private CategoryRepository $categoryRepository;

    public function __construct()
    {
        $this->productRepository = new ProductRepository();
        $this->categoryRepository = new categoryRepository();
        parent::__construct();
    }

    private function paginationHandle(int $page = 0, array &$args, bool $getProducts, ?Paginator $products)
    {
        $totalItems = 0;
        if ($getProducts == false && $products) {
            $totalItems = count($products);
        } else {
            $totalItems = $this->productRepository->getTotalProductsNumber();
        }

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

        if ($getProducts) {
            $productsPagination = $this->productRepository->getProductsPagination($page);
            $this->args['products'] = $productsPagination;
        }

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
    }

    public function productsPage()
    {
        $template = 'products.html.twig';
        $page = filter_input(INPUT_GET, "page", FILTER_VALIDATE_INT);

        if ($page === null || $page < 0) {
            $page = 0;
        }

        $this->paginationHandle($page, $this->args, true, null);

        print $this->twig->render($template, $this->args);
    }

    public function productDetailPage($id)
    {
        $template = 'product-detail.html.twig';
        $product = $this->productRepository->find($id);

        $this->args['product'] = $product;

        print $this->twig->render($template, $this->args);
    }

    public function productsFilter()
    {
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
        $categoryNames = filter_input(INPUT_GET, 'categories', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?? [];
        $products = [];
        $template = 'products.html.twig';

        if (count($categoryNames) > 0) {
            $products = $this->productRepository->findWithCategoriesPaginated($categoryNames, $page ? $page : 0);
            $this->paginationHandle($page ? $page : 0, $this->args, false, $products);
            $this->args['products'] = $products;
            $this->args['products_filter'] = true;
            $this->args['current_url'] = $_SERVER['REQUEST_URI'];
            print $this->twig->render($template, $this->args);
        }

    }

}

?>
