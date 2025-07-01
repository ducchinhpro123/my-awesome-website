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

    private const PRICE_RANGES = [
        ['label' => '0₫ - 500.000₫', 'value' => '0-500000'],
        ['label' => '500.000₫ - 1.000.000₫', 'value' => '500000-1000000'],
        ['label' => '1.000.000₫ - 5.000.000₫', 'value' => '1000000-5000000'],
        ['label' => '5.000.000₫ +', 'value' => '5000000+'],
    ];

    private function paginationHandle(int $page = 0, array &$args, bool $getProducts, Paginator|array|null $products)
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
        $this->args['prices'] = self::PRICE_RANGES;
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
        $categoryName = filter_input(INPUT_GET, 'category', FILTER_DEFAULT);
        $prices = filter_input(INPUT_GET, 'prices', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?? [];

        // Gather criteria for a single call to query
        $criteria = [];
        $allCategoriesFilter = [];
        if (!empty($prices)) {
            $criteria['prices'] = $prices;
        }
        if (!empty($categoryNames)) {
            $allCategoriesFilter = $categoryNames;
        }
        if (!empty($categoryName)) {
            $allCategoriesFilter[] = $categoryName;
        }
        if (!empty($allCategoriesFilter)) {
            $criteria['categories'] = array_unique($allCategoriesFilter);
        }

        $template = 'products.html.twig';

        $products = $this->productRepository->findWithFilters($criteria, $page ? $page : 0);
        $this->args['products'] = $products;
        $this->args['categories_filter'] = $categoryNames;
        $this->args['category_filter'] = $categoryName;
        error_log($categoryName);
        $this->args['products_filter'] = true; // mark as a way to handle the pagination in twig template
        $this->args['prices_filter'] = $prices;

        $this->args['current_url'] = $_SERVER['REQUEST_URI'];
        $this->paginationHandle($page ? $page : 0, $this->args, false, $products);
        print $this->twig->render($template, $this->args);

    }

}

?>
