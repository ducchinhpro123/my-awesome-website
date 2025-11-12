<?php

namespace MyAwesomeWebsite\controller;

use Doctrine\ORM\Tools\Pagination\Paginator;

use MyAwesomeWebsite\repository\ProductRepository;
use MyAwesomeWebsite\Controller;
use MyAwesomeWebsite\repository\CategoryRepository;

class ProductController extends Controller
{
    private ProductRepository $productRepository;

    public function __construct()
    {
        $this->productRepository = new ProductRepository();
        $this->categoryRepository = new CategoryRepository();
        parent::__construct();
    }

    private const ORDER_BY = [
        ['label' => 'Sắp xếp: Nổi bật', 'value' => 'featured'],
        ['label' => 'Giá: Thấp đến Cao', 'value' => 'price_asc'],
        ['label' => 'Giá: Cao đến Thấp', 'value' => 'price_desc'],
        ['label' => 'Tên: A đến Z', 'value' => 'name_asc'],
        ['label' => 'Tên: Z đến A', 'value' => 'name_desc'],
        ['label' => 'Đánh giá: Cao đến Thấp', 'value' => 'rating_asc'],
    ];

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
        $this->args['orderBy'] = self::ORDER_BY;
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
        $sortBy = filter_input(INPUT_GET, 'sort_by', FILTER_DEFAULT, FILTER_SANITIZE_STRING);
        $searchTerm = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_STRING);

        // Gather criteria for a single call to query
        $criteria = [];
        $allCategoriesFilter = [];
        if (!empty($searchTerm)) {
            $criteria['searchTerm'] = $searchTerm;
            $this->args['searchTerm'] = $searchTerm;
        }
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

        if (!empty($sortBy)) {
            $criteria['sort_by'] = $sortBy;
            $this->args['sort_by_filter'] = $sortBy;
        }

        // Apply criteria in repository
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
