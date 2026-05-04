CREATE DATABASE IF NOT EXISTS `banking` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `banking`;

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE IF NOT EXISTS `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `surname` varchar(20) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`id`, `name`, `surname`, `currency`, `created_at`) VALUES
(1, 'Paride', 'Ficiente', 'USD', '2026-04-27 06:38:25'),
(2, 'Mimas', 'Turbo', 'EUR', '2026-04-27 06:38:25'),
(3, 'Musso', 'Leeni', 'USD', '2026-04-27 06:38:25'),
(4, 'Lamin', 'Kiadura', 'YEN', '2026-04-27 06:38:25');

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE IF NOT EXISTS `transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `type` enum('deposit','withdrawal') NOT NULL,
  `amount` int(11) NOT NULL,
  `description` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`id`, `account_id`, `type`, `amount`, `description`, `created_at`) VALUES
(1, 1, 'deposit', 500, 'Initial deposit', '2026-04-27 06:38:25'),
(2, 1, 'withdrawal', 200, 'ATM withdrawal', '2026-04-27 06:38:25'),
(3, 2, 'deposit', 500, 'Salary', '2026-04-27 06:38:25'),
(4, 2, 'withdrawal', 150, 'Online purchase', '2026-04-27 06:38:25'),
(5, 3, 'deposit', 100, 'Gift', '2026-04-27 06:38:25'),
(6, 4, 'withdrawal', 350, 'Nigeriana', '2026-04-27 06:38:25'),
(7, 4, 'withdrawal', 650, 'Neve', '2026-04-27 06:38:25');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`) ON DELETE CASCADE;
COMMIT;
