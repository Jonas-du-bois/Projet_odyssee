-- Schéma SQL mis à jour avec le système polymorphique Breitling League
-- Généré le 10 juin 2025

CREATE TABLE `ranks` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `level` int NOT NULL,
  `minimum_points` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `users` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `rank_id` bigint UNSIGNED NULL,
  `registration_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) NULL
);

CREATE TABLE `chapters` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NULL,
  `theory_content` longtext NULL,
  `is_active` boolean NOT NULL DEFAULT true,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `units` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `chapter_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NULL,
  `theory_html` text NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

-- Table Questions avec système polymorphique
CREATE TABLE `questions` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `quizable_type` varchar(255) NULL,
  `quizable_id` bigint UNSIGNED NULL,
  `question_text` text NULL,
  `options` json NULL,
  `correct_answer` varchar(255) NULL,
  `timer_seconds` int NULL,
  `type` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  INDEX `questions_quizable_type_quizable_id_index` (`quizable_type`, `quizable_id`)
);

CREATE TABLE `choices` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `question_id` bigint UNSIGNED NOT NULL,
  `text` varchar(255) NOT NULL,
  `is_correct` boolean NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `discoveries` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `chapter_id` bigint UNSIGNED NOT NULL,
  `available_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `novelties` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `chapter_id` bigint UNSIGNED NOT NULL,
  `publication_date` date NOT NULL,
  `initial_bonus` boolean NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `events` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `theme` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `event_units` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `event_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `reminders` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `chapter_id` bigint UNSIGNED NOT NULL,
  `number_questions` int NOT NULL,
  `deadline_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `weeklies` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `chapter_id` bigint UNSIGNED NOT NULL,
  `week_start` date NOT NULL,
  `number_questions` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `last_chances` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

-- Table QuizTypes avec morph_type pour le système polymorphique
CREATE TABLE `quiz_types` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `morph_type` varchar(255) NULL,
  `base_points` int NOT NULL,
  `speed_bonus` boolean NOT NULL,
  `gives_ticket` boolean NOT NULL,
  `bonus_multiplier` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

-- Table QuizInstances avec système polymorphique
CREATE TABLE `quiz_instances` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `quiz_type_id` bigint UNSIGNED NOT NULL,
  `quizable_type` varchar(255) NULL,
  `quizable_id` bigint UNSIGNED NULL,
  `quiz_mode` varchar(255) NOT NULL DEFAULT 'quiz',
  `launch_date` datetime NOT NULL,
  `speed_bonus` boolean NOT NULL DEFAULT false,
  `status` enum('active', 'completed', 'abandoned') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  INDEX `quiz_instances_quizable_type_quizable_id_index` (`quizable_type`, `quizable_id`)
);

CREATE TABLE `user_quiz_scores` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `quiz_instance_id` bigint UNSIGNED NOT NULL,
  `total_points` int NOT NULL,
  `total_time` int NOT NULL,
  `ticket_obtained` boolean NOT NULL,
  `bonus_obtained` boolean NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

-- Table UserAnswers avec quiz_instance_id
CREATE TABLE `user_answers` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `quiz_instance_id` bigint UNSIGNED NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `choice_id` bigint UNSIGNED NULL,
  `is_correct` boolean NOT NULL,
  `response_time` int NOT NULL,
  `points_obtained` int NOT NULL,
  `date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `scores` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `total_points` int NOT NULL,
  `bonus_points` int NOT NULL,
  `rank_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `progress` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `chapter_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NULL,
  `percentage` float NOT NULL,
  `completed` boolean NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `lottery_tickets` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `weekly_id` bigint UNSIGNED NOT NULL,
  `obtained_date` date NOT NULL,
  `bonus` boolean NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `weekly_series` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `count` int NOT NULL,
  `bonus_tickets` int NOT NULL,
  `last_participation` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `read` boolean NOT NULL,
  `date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

-- Tables Laravel par défaut
CREATE TABLE `cache` (
  `key` varchar(255) PRIMARY KEY,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL
);

CREATE TABLE `cache_locks` (
  `key` varchar(255) PRIMARY KEY,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL
);

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  INDEX `jobs_queue_index` (`queue`)
);

CREATE TABLE `job_batches` (
  `id` varchar(255) PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext NULL,
  `cancelled_at` int NULL,
  `created_at` int NOT NULL,
  `finished_at` int NULL
);

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL UNIQUE,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL UNIQUE,
  `abilities` text NULL,
  `last_used_at` timestamp NULL,
  `expires_at` timestamp NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  INDEX `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`, `tokenable_id`)
);

-- Contraintes de clés étrangères

ALTER TABLE `users` ADD FOREIGN KEY (`rank_id`) REFERENCES `ranks` (`id`) ON DELETE SET NULL;

ALTER TABLE `units` ADD FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE;

ALTER TABLE `choices` ADD FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

ALTER TABLE `discoveries` ADD FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE;

ALTER TABLE `novelties` ADD FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE;

ALTER TABLE `event_units` ADD FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;
ALTER TABLE `event_units` ADD FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;

ALTER TABLE `reminders` ADD FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE;

ALTER TABLE `weeklies` ADD FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE;

ALTER TABLE `quiz_instances` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `quiz_instances` ADD FOREIGN KEY (`quiz_type_id`) REFERENCES `quiz_types` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_quiz_scores` ADD FOREIGN KEY (`quiz_instance_id`) REFERENCES `quiz_instances` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_answers` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `user_answers` ADD FOREIGN KEY (`quiz_instance_id`) REFERENCES `quiz_instances` (`id`) ON DELETE CASCADE;
ALTER TABLE `user_answers` ADD FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;
ALTER TABLE `user_answers` ADD FOREIGN KEY (`choice_id`) REFERENCES `choices` (`id`) ON DELETE SET NULL;

ALTER TABLE `scores` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `scores` ADD FOREIGN KEY (`rank_id`) REFERENCES `ranks` (`id`) ON DELETE RESTRICT;

ALTER TABLE `progress` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `progress` ADD FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE;
ALTER TABLE `progress` ADD FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

ALTER TABLE `lottery_tickets` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `lottery_tickets` ADD FOREIGN KEY (`weekly_id`) REFERENCES `weeklies` (`id`) ON DELETE CASCADE;

ALTER TABLE `weekly_series` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `notifications` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
