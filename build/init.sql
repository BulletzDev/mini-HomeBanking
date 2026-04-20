CREATE TABLE `accounts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `owner_name` VARCHAR(20) NOT NULL,
  `currency` INT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `transactions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) NOT NULL,
  `type` VARCHAR(100) NOT NULL,
  `votazione` INT(3) NOT NULL CHECK (`votazione` BETWEEN 0 AND 100),
  `ente` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`alunno_id`) REFERENCES `alunni`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

