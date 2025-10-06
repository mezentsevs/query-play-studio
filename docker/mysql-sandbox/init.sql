CREATE DATABASE IF NOT EXISTS `mysql_sandbox` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `mysql_sandbox`;

CREATE TABLE IF NOT EXISTS `sandbox_info` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `message` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sandbox_info` (`message`) VALUES ('MySQL Sandbox is ready for user experiments');
