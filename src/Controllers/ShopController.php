<?php

namespace Alexartwww\Betoola\Controllers;

use Alexartwww\Betoola\Interfaces\DBServiceInterface;
use Alexartwww\Betoola\Interfaces\ShopServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

class ShopController
{
    private ShopServiceInterface $shopService;
    private DBServiceInterface $dbService;

    public function __construct(ShopServiceInterface $shopService, DBServiceInterface $dbService)
    {
        $this->shopService = $shopService;
        $this->dbService = $dbService;
    }

    public function home(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response->getBody()->write('<html><body><h1>Hello world!</h1><p>'.date('c').'</p></body></html>');
        return $response;
    }

    public function getCategories(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $categories = $this->shopService->getCategories();
        $response->getBody()->write(json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getProducts(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $products = $this->shopService->getProducts();
        $response->getBody()->write(json_encode($products, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getPrices(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $prices = $this->shopService->getPrices();
        $response->getBody()->write(json_encode($prices, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function postPrice(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!is_array($request->getHeader('Accept')) || ($request->getHeader('Accept')[0] != 'application/json')) {
            throw new HttpBadRequestException($request, 'API supports only application/json. Please set it in Accept http header.');
        }
        $body = $request->getBody();
        $bodyAssoc = json_decode($body, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new HttpBadRequestException($request, 'Can not parse json. ' . json_last_error_msg());
        }
        if (!isset($bodyAssoc['product_name']) && !isset($bodyAssoc['product_id'])) {
            throw new HttpBadRequestException($request, 'Request must have product_name or product_id in body.');
        }
        if (!isset($bodyAssoc['category_name']) && !isset($bodyAssoc['category_id'])) {
            throw new HttpBadRequestException($request, 'Request must have category_name or category_id in body.');
        }
        if (!isset($bodyAssoc['variant'])) {
            throw new HttpBadRequestException($request, 'Request must have variant in body.');
        }
        if (!isset($bodyAssoc['currency'])) {
            throw new HttpBadRequestException($request, 'Request must have currency in body.');
        }
        if (!isset($bodyAssoc['price'])) {
            throw new HttpBadRequestException($request, 'Request must have price in body.');
        }
        if (isset($bodyAssoc['product_id']) && !preg_match("/^\d+$/", $bodyAssoc['product_id'])) {
            throw new HttpBadRequestException($request, 'product_id must be positive integer.');
        }
        if (isset($bodyAssoc['category_id']) && !preg_match("/^\d+$/", $bodyAssoc['category_id'])) {
            throw new HttpBadRequestException($request, 'category_id must be positive integer.');
        }
        if (!preg_match("/^\d+(.\d+)?$/", $bodyAssoc['price']) || floatval($bodyAssoc['price']) <= 0) {
            throw new HttpBadRequestException($request, 'price must be positive float.');
        }

        $this->dbService->beginTransaction();

        if (isset($bodyAssoc['category_name']) && !isset($bodyAssoc['category_id'])) {
            $category = $this->shopService->getCategoryByName($bodyAssoc['category_name']);
            if (!$category) {
                $this->dbService->rollBack();
                throw new HttpBadRequestException($request, 'category_name does not exist ' . $bodyAssoc['category_name']);
            }
        } else {
            $category = $this->shopService->getCategoryById(intval($bodyAssoc['category_id']));
            if (!$category) {
                $this->dbService->rollBack();
                throw new HttpBadRequestException($request, 'category_id does not exist ' . $bodyAssoc['category_id']);
            }
        }
        $categoryId = $category['id'];

        if (isset($bodyAssoc['product_name']) && !isset($bodyAssoc['product_id'])) {
            $product = $this->shopService->getProductByName($categoryId, $bodyAssoc['product_name']);
            if (!$product) {
                $productId = $this->shopService->createProduct($categoryId, $bodyAssoc['product_name']);
            } else {
                $productId = $product['id'];
            }
        } else {
            $product = $this->shopService->getProductById($categoryId, intval($bodyAssoc['product_id']));
            if (!$product) {
                $this->dbService->rollBack();
                throw new HttpBadRequestException($request, 'product_id does not exist ' . $bodyAssoc['product_id']);
            } else {
                $productId = $product['id'];
            }
        }

        $variant = $bodyAssoc['variant'];
        $currency = $bodyAssoc['currency'];
        $price = $bodyAssoc['price'];

        $currentPrice = $this->shopService->getPrice($productId, $variant, $currency);
        if ($currentPrice) {
            $this->dbService->rollBack();
            throw new HttpBadRequestException($request, 'This product aready have price for variant ' . $currentPrice['variant'] . ' for currency ' . $currency . ' = ' . $price);
        } else {
            $this->shopService->setPrice($productId, $variant, $currency, $price);
        }

        $this->dbService->commit();
        $response->getBody()->write(json_encode(['statusCode' => 201], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }
}
