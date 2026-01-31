-- Run after project.sql. Adds booking, remember_tokens, user complaints, feedback.
-- Database: project (use it before running: USE project;)

USE project;

-- Payment type (cash payment) for admin panel
ALTER TABLE `payment` ADD COLUMN `pay_type` varchar(50) DEFAULT NULL;

-- Booking (user room booking)
CREATE TABLE IF NOT EXISTS `booking` (
  `b_id` int NOT NULL AUTO_INCREMENT,
  `u_id` int NOT NULL,
  `room_id` int NOT NULL,
  `h_id` int DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `amount` decimal(10,2) DEFAULT NULL,
  `book_date` date DEFAULT NULL,
  `check_in` date DEFAULT NULL,
  `check_out` date DEFAULT NULL,
  PRIMARY KEY (`b_id`),
  KEY `u_id` (`u_id`),
  KEY `room_id` (`room_id`),
  KEY `h_id` (`h_id`),
  CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `users` (`u_id`),
  CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `room` (`room_id`),
  CONSTRAINT `booking_ibfk_3` FOREIGN KEY (`h_id`) REFERENCES `hostel` (`h_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Remember me tokens (session cookie for users)
CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`u_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- User complaints: add u_id for registered users (run once; if u_id already exists, skip these two lines)
ALTER TABLE `complaint` ADD COLUMN `u_id` int DEFAULT NULL;
ALTER TABLE `complaint` ADD CONSTRAINT `complaint_u_id_fk` FOREIGN KEY (`u_id`) REFERENCES `users` (`u_id`);

-- Feedback table (project.sql doesn't have it)
CREATE TABLE IF NOT EXISTS `feedback` (
  `f_id` int NOT NULL AUTO_INCREMENT,
  `u_id` int DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rating` int DEFAULT 5,
  `message` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`f_id`),
  KEY `u_id` (`u_id`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `users` (`u_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- FAQ (project.sql doesn't have it)
CREATE TABLE IF NOT EXISTS `faq` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- 1st FAQ links to Booking, 2nd to Rooms page, 3rd to Fee page
INSERT INTO `faq` (`question`, `answer`, `sort_order`) VALUES
('How do I book a room?', 'Go to the Booking page to select a room and check-in date. You can also apply from the Rooms page.', 1),
('What types of rooms are available?', 'See the Rooms page for room types, capacity, and availability.', 2),
('What is the fee structure?', 'See the Fee page for monthly and annual rates by room type.', 3);

