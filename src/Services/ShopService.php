<?php

namespace Alexartwww\Betoola\Services;

use Alexartwww\Betoola\Interfaces\DBServiceInterface;
use Alexartwww\Betoola\Interfaces\ShopServiceInterface;

class ShopService implements ShopServiceInterface
{
    private $dbService;

    public function __construct(DBServiceInterface $dbService)
    {
        $this->dbService = $dbService;
    }

    public function getCategories(): array
    {
        $sth = $this->dbService->prepare('
            SELECT
                `id`,
                `created_at`,
                `updated_at`,
                `name`
            FROM
                `categories`
            ORDER BY
                `id`
        ');
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getProducts(): array
    {
        $sth = $this->dbService->prepare('
            SELECT
                P.`id`,
                P.`created_at`,
                P.`updated_at`,
                P.`category_id`,
                C.`name` AS `category_name`,
                P.`name`
            FROM
                `products` P
                INNER JOIN `categories` C ON C.`id` = P.`category_id`
            ORDER BY
                P.`id`
        ');
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getPrices(): array
    {
        $sth = $this->dbService->prepare('
            SELECT
                PR.`id`,
                PR.`created_at`,
                PR.`updated_at`,
                P.`category_id`,
                C.`name` AS `category_name`,
                PR.`product_id`,
                P.`name` AS `product_name`,
                PR.`variant`,
                PR.`currency`,
                PR.`price`
            FROM
                `prices` PR
                INNER JOIN `products` P ON PR.`product_id` = P.`id`
                INNER JOIN `categories` C ON C.`id` = P.`category_id`
            ORDER BY
                PR.`id`
        ');
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getCategoryById(int $id): array|bool
    {
        $sth = $this->dbService->prepare('
            SELECT
                `id`,
                `created_at`,
                `updated_at`,
                `name`
            FROM
                `categories`
            WHERE
                `id` = ?
            ORDER BY
                `id`
            LIMIT 1
        ');
        $sth->execute([$id]);
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    public function getCategoryByName(string $name): array|bool
    {
        $sth = $this->dbService->prepare('
            SELECT
                `id`,
                `created_at`,
                `updated_at`,
                `name`
            FROM
                `categories`
            WHERE
                `name` = ?
            ORDER BY
                `id`
            LIMIT 1
        ');
        $sth->execute([$name]);
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    public function getProductById(int $categoryId, int $id): array|bool
    {
        $sth = $this->dbService->prepare('
            SELECT
                P.`id`,
                P.`created_at`,
                P.`updated_at`,
                P.`category_id`,
                C.`name` AS `category_name`,
                P.`name`
            FROM
                `products` P
                INNER JOIN `categories` C ON C.`id` = P.`category_id`
            WHERE
                C.`id` = ?
                AND P.`id` = ?
            ORDER BY
                P.`id`
            LIMIT 1
        ');
        $sth->execute([$categoryId, $id]);
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    public function getProductByName(int $categoryId, string $name): array|bool
    {
        $sth = $this->dbService->prepare('
            SELECT
                P.`id`,
                P.`created_at`,
                P.`updated_at`,
                P.`category_id`,
                C.`name` AS `category_name`,
                P.`name`
            FROM
                `products` P
                INNER JOIN `categories` C ON C.`id` = P.`category_id`
            WHERE
                C.`id` = ?
                AND P.`name` = ?
            ORDER BY
                P.`id`
            LIMIT 1
        ');
        $sth->execute([$categoryId, $name]);
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    public function createProduct(int $categoryId, string $name): int
    {
        $sth = $this->dbService->prepare('
            INSERT INTO `products` (`category_id`, `name`) VALUES (?, ?)
        ');
        $sth->execute([$categoryId, $name]);
        return intval($this->dbService->lastInsertId());
    }


    public function getPrice(int $productId, string $variant, string $currency): array|bool
    {
        $sth = $this->dbService->prepare('
            SELECT
                PR.`id`,
                PR.`created_at`,
                PR.`updated_at`,
                P.`category_id`,
                C.`name` AS `category_name`,
                PR.`product_id`,
                P.`name` AS `product_name`,
                PR.`variant`,
                PR.`currency`,
                PR.`price`
            FROM
                `prices` PR
                INNER JOIN `products` P ON PR.`product_id` = P.`id`
                INNER JOIN `categories` C ON C.`id` = P.`category_id`
            WHERE
                PR.`product_id` = ?
                AND PR.`variant` = ?
                AND PR.`currency` = ?
            ORDER BY
                PR.`id`
        ');
        $sth->execute([$productId, $variant, $currency]);
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    public function setPrice(int $productId, string $variant, string $currency, float $price): bool
    {
        $sth = $this->dbService->prepare('
            INSERT INTO
            `prices`
                (`product_id`,
                 `variant`,
                 `currency`,
                 `price`)
            VALUES
                (?, ?, ?, ?)
        ');
        return $sth->execute([$productId, $variant, $currency, $price]);
    }
}
