CREATE TABLE `account` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(20) NOT NULL,
  `surname` VARCHAR(20) NOT NULL,
  `currency` VARCHAR(10) NOT NULL,
  'balance' INT(254) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `transaction` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) NOT NULL,
  CHECK (`type` IN ('deposit','withdrawal')) NOT NULL,
  `amount` INT(11) NOT NULL
  `description` VARCHAR(100) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`account_id`) REFERENCES `account`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `account` (`name`,'surname', `currency`, `balance`) VALUES
  ('Paride','Ficiente','USD',1000),
  ('Mimas','Turbo','EUR',500),
  ('Musso','Leeni','USD',0),
  ('Lamin','Kiadura','YEN',1000);

INSERT INTO `transaction` (`account_id`, `type`, `amount`, `description`) VALUES
  (1, 'deposit',    500, 'Initial deposit'),
  (1, 'withdrawal', 200, 'ATM withdrawal'),
  (2, 'deposit',    500, 'Salary'),
  (2, 'withdrawal', 150, 'Online purchase'),
  (3, 'deposit',    100, 'Gift');
  (4, 'withdrawal',    350, 'Nigeriana');
  (4, 'withdrawal',    650, 'Neve');