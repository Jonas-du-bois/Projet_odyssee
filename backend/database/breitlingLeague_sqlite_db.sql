CREATE TABLE `User` (
  `id` int PRIMARY KEY,
  `nom` varchar(255),
  `email` varchar(255),
  `mot_de_passe` varchar(255),
  `rang_id` int,
  `date_inscription` date
);

CREATE TABLE `Rank` (
  `id` int PRIMARY KEY,
  `nom` varchar(255),
  `niveau` int,
  `points_minimum` int
);

CREATE TABLE `Chapter` (
  `id` int PRIMARY KEY,
  `titre` varchar(255),
  `description` text
);

CREATE TABLE `Unit` (
  `id` int PRIMARY KEY,
  `chapter_id` int,
  `titre` varchar(255),
  `description` text,
  `theorie_html` text
);

CREATE TABLE `Question` (
  `id` int PRIMARY KEY,
  `unit_id` int,
  `enonce` text,
  `timer_secondes` int,
  `type` varchar(255)
);

CREATE TABLE `Choice` (
  `id` int PRIMARY KEY,
  `question_id` int,
  `texte` varchar(255),
  `est_correct` boolean
);

CREATE TABLE `Discovery` (
  `id` int PRIMARY KEY,
  `chapter_id` int,
  `date_disponible` date
);

CREATE TABLE `Novelty` (
  `id` int PRIMARY KEY,
  `chapter_id` int,
  `date_publication` date,
  `bonus_initial` boolean
);

CREATE TABLE `Event` (
  `id` int PRIMARY KEY,
  `theme` varchar(255),
  `date_debut` date,
  `date_fin` date
);

CREATE TABLE `EventUnit` (
  `id` int PRIMARY KEY,
  `event_id` int,
  `unit_id` int
);

CREATE TABLE `Reminder` (
  `id` int PRIMARY KEY,
  `chapter_id` int,
  `nb_questions` int,
  `date_limite` date
);

CREATE TABLE `Weekly` (
  `id` int PRIMARY KEY,
  `chapter_id` int,
  `semaine` date,
  `nb_questions` int
);

CREATE TABLE `LastChance` (
  `id` int PRIMARY KEY,
  `nom` varchar(255),
  `date_debut` date,
  `date_fin` date
);

CREATE TABLE `QuizType` (
  `id` int PRIMARY KEY,
  `nom` varchar(255),
  `base_points` int,
  `bonus_rapidite` boolean,
  `donne_ticket` boolean,
  `multiplicateur_bonus` int
);

CREATE TABLE `QuizInstance` (
  `id` int PRIMARY KEY,
  `user_id` int,
  `quiz_type_id` int,
  `module_type` varchar(255),
  `module_id` int,
  `date_lancement` datetime
);

CREATE TABLE `UserQuizScore` (
  `id` int PRIMARY KEY,
  `quiz_instance_id` int,
  `total_points` int,
  `temps_total` int,
  `ticket_obtenu` boolean,
  `bonus_obtenu` boolean
);

CREATE TABLE `UserAnswer` (
  `id` int PRIMARY KEY,
  `user_id` int,
  `question_id` int,
  `choix_id` int,
  `est_correct` boolean,
  `temps_reponse` int,
  `points_obtenus` int,
  `date` datetime
);

CREATE TABLE `Score` (
  `id` int PRIMARY KEY,
  `user_id` int,
  `points_total` int,
  `points_bonus` int,
  `rang_id` int
);

CREATE TABLE `Progress` (
  `id` int PRIMARY KEY,
  `user_id` int,
  `chapter_id` int,
  `unit_id` int,
  `pourcentage` float,
  `terminé` boolean
);

CREATE TABLE `LotteryTicket` (
  `id` int PRIMARY KEY,
  `user_id` int,
  `weekly_id` int,
  `date_obtenue` date,
  `bonus` boolean
);

CREATE TABLE `WeeklySeries` (
  `id` int PRIMARY KEY,
  `user_id` int,
  `count` int,
  `bonus_tickets` int,
  `derniere_participation` date
);

CREATE TABLE `Notification` (
  `id` int PRIMARY KEY,
  `user_id` int,
  `type` varchar(255),
  `message` text,
  `lu` boolean,
  `date` datetime
);

ALTER TABLE `User` ADD FOREIGN KEY (`rang_id`) REFERENCES `Rank` (`id`);

ALTER TABLE `Unit` ADD FOREIGN KEY (`chapter_id`) REFERENCES `Chapter` (`id`);

ALTER TABLE `Question` ADD FOREIGN KEY (`unit_id`) REFERENCES `Unit` (`id`);

ALTER TABLE `Choice` ADD FOREIGN KEY (`question_id`) REFERENCES `Question` (`id`);

ALTER TABLE `Discovery` ADD FOREIGN KEY (`chapter_id`) REFERENCES `Chapter` (`id`);

ALTER TABLE `Novelty` ADD FOREIGN KEY (`chapter_id`) REFERENCES `Chapter` (`id`);

ALTER TABLE `EventUnit` ADD FOREIGN KEY (`event_id`) REFERENCES `Event` (`id`);

ALTER TABLE `EventUnit` ADD FOREIGN KEY (`unit_id`) REFERENCES `Unit` (`id`);

ALTER TABLE `Reminder` ADD FOREIGN KEY (`chapter_id`) REFERENCES `Chapter` (`id`);

ALTER TABLE `Weekly` ADD FOREIGN KEY (`chapter_id`) REFERENCES `Chapter` (`id`);

ALTER TABLE `QuizInstance` ADD FOREIGN KEY (`user_id`) REFERENCES `User` (`id`);

ALTER TABLE `QuizInstance` ADD FOREIGN KEY (`quiz_type_id`) REFERENCES `QuizType` (`id`);

ALTER TABLE `UserQuizScore` ADD FOREIGN KEY (`quiz_instance_id`) REFERENCES `QuizInstance` (`id`);

ALTER TABLE `UserAnswer` ADD FOREIGN KEY (`user_id`) REFERENCES `User` (`id`);

ALTER TABLE `UserAnswer` ADD FOREIGN KEY (`question_id`) REFERENCES `Question` (`id`);

ALTER TABLE `UserAnswer` ADD FOREIGN KEY (`choix_id`) REFERENCES `Choice` (`id`);

ALTER TABLE `Score` ADD FOREIGN KEY (`user_id`) REFERENCES `User` (`id`);

ALTER TABLE `Score` ADD FOREIGN KEY (`rang_id`) REFERENCES `Rank` (`id`);

ALTER TABLE `Progress` ADD FOREIGN KEY (`user_id`) REFERENCES `User` (`id`);

ALTER TABLE `Progress` ADD FOREIGN KEY (`chapter_id`) REFERENCES `Chapter` (`id`);

ALTER TABLE `Progress` ADD FOREIGN KEY (`unit_id`) REFERENCES `Unit` (`id`);

ALTER TABLE `LotteryTicket` ADD FOREIGN KEY (`user_id`) REFERENCES `User` (`id`);

ALTER TABLE `LotteryTicket` ADD FOREIGN KEY (`weekly_id`) REFERENCES `Weekly` (`id`);

ALTER TABLE `WeeklySeries` ADD FOREIGN KEY (`user_id`) REFERENCES `User` (`id`);

ALTER TABLE `Notification` ADD FOREIGN KEY (`user_id`) REFERENCES `User` (`id`);
