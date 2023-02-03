DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories`
(
    `id`         SERIAL PRIMARY KEY,
    `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `name`       varchar(255) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

INSERT INTO `categories` (`id`, `name`) VALUES (1, "Shoes"), (2, "Jewelry");

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products`
(
    `id`          SERIAL PRIMARY KEY,
    `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `category_id` BIGINT UNSIGNED,
    `name`        varchar(255) NOT NULL,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `prices`;
CREATE TABLE `prices`
(
    `id`         SERIAL PRIMARY KEY,
    `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `product_id` BIGINT UNSIGNED,
    `variant`    varchar(255) NOT NULL,
    `currency`   varchar(20) NOT NULL,
    `price`      DECIMAL(10,2),
    UNIQUE KEY `product_id_variant_currency` (`product_id`, `variant`, `currency`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
