CREATE DATABASE IF NOT EXISTS `book_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `book_db`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(191) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(191) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'user',
  `wallet_balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `requires_password_change` TINYINT(1) NOT NULL DEFAULT 0,
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `reset_token_expiry` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `books` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `author` VARCHAR(191) NOT NULL,
  `publisher` VARCHAR(191) NOT NULL,
  `genre` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `publish_date` DATE DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `pdf_path` VARCHAR(255) DEFAULT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `is_free` TINYINT(1) NOT NULL DEFAULT 1,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_earnings` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_books_user_id` (`user_id`),
  KEY `idx_books_status` (`status`),
  KEY `idx_books_genre` (`genre`),
  CONSTRAINT `fk_books_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `book_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` VARCHAR(50) NOT NULL,
  `payment_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `payment_id` VARCHAR(191) DEFAULT NULL,
  `author_earning` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `platform_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_orders_book_id` (`book_id`),
  KEY `idx_orders_user_id` (`user_id`),
  KEY `idx_orders_payment_status` (`payment_status`),
  KEY `idx_orders_payment_id` (`payment_id`),
  CONSTRAINT `fk_orders_book`
    FOREIGN KEY (`book_id`) REFERENCES `books` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_orders_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_library` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `book_id` INT UNSIGNED NOT NULL,
  `order_id` INT UNSIGNED DEFAULT NULL,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_library_user_book` (`user_id`, `book_id`),
  KEY `idx_user_library_book_id` (`book_id`),
  KEY `idx_user_library_order_id` (`order_id`),
  CONSTRAINT `fk_user_library_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_user_library_book`
    FOREIGN KEY (`book_id`) REFERENCES `books` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_user_library_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wallet_transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `type` VARCHAR(20) DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `reference_id` VARCHAR(191) DEFAULT NULL,
  `payment_id` VARCHAR(191) DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'completed',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_wallet_transactions_user_id` (`user_id`),
  KEY `idx_wallet_transactions_created_at` (`created_at`),
  CONSTRAINT `fk_wallet_transactions_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
