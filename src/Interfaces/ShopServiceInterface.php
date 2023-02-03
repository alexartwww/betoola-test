<?php
namespace Alexartwww\Betoola\Interfaces;


interface ShopServiceInterface
{
    public function getCategories(): array;

    public function getProducts(): array;

    public function getPrices(): array;

    public function getCategoryById(int $id): array|bool;

    public function getCategoryByName(string $name): array|bool;

    public function getProductById(int $categoryId, int $id): array|bool;

    public function getProductByName(int $categoryId, string $name): array|bool;

    public function createProduct(int $categoryId, string $name): int;

    public function getPrice(int $productId, string $variant, string $currency): array|bool;

    public function setPrice(int $productId, string $variant, string $currency, float $price): bool;
}
