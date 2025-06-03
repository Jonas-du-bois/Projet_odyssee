-- Script SQLite pour créer la base de données Breilting League
-- Adapté pour SQLite depuis le schéma MySQL

CREATE TABLE IF NOT EXISTS `User` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `nom` TEXT,
  `email` TEXT,
  `mot_de_passe` TEXT,
  `rang_id` INTEGER,
  `date_inscription` DATE
);

CREATE TABLE IF NOT EXISTS `Rank` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `nom` TEXT,
  `niveau` INTEGER,
  `points_minimum` INTEGER
);

CREATE TABLE IF NOT EXISTS `Chapter` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `titre` TEXT,
  `description` TEXT
);

CREATE TABLE IF NOT EXISTS `Unit` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `chapter_id` INTEGER,
  `titre` TEXT,
  `description` TEXT
);

CREATE TABLE IF NOT EXISTS `Question` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `unit_id` INTEGER,
  `enonce` TEXT,
  `timer_secondes` INTEGER,
  `type` TEXT
);

CREATE TABLE IF NOT EXISTS `Choice` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `question_id` INTEGER,
  `texte` TEXT,
  `est_correct` INTEGER
);

CREATE TABLE IF NOT EXISTS `Discovery` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `chapter_id` INTEGER,
  `date_disponible` DATE
);

CREATE TABLE IF NOT EXISTS `Novelty` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `chapter_id` INTEGER,
  `date_publication` DATE,
  `bonus_initial` INTEGER 
);

CREATE TABLE IF NOT EXISTS `Event` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `chapter_id` INTEGER,
  `theme` TEXT,
  `date_debut` DATE,
  `date_fin` DATE
);

CREATE TABLE IF NOT EXISTS `Reminder` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `chapter_id` INTEGER,
  `nb_questions` INTEGER,
  `date_limite` DATE
);

CREATE TABLE IF NOT EXISTS `Weekly` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `chapter_id` INTEGER,
  `semaine` DATE,
  `nb_questions` INTEGER
);

CREATE TABLE IF NOT EXISTS `LastChance` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `nom` TEXT,
  `date_debut` DATE,
  `date_fin` DATE
);

CREATE TABLE IF NOT EXISTS `QuizType` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `nom` TEXT,
  `base_points` INTEGER,
  `bonus_rapidite` INTEGER,
  `donne_ticket` INTEGER,
  `multiplicateur_bonus` INTEGER
);

CREATE TABLE IF NOT EXISTS `QuizInstance` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER,
  `quiz_type_id` INTEGER,
  `module_type` TEXT,
  `module_id` INTEGER,
  `date_lancement` DATETIME
);

CREATE TABLE IF NOT EXISTS `UserQuizScore` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `quiz_instance_id` INTEGER,
  `total_points` INTEGER,
  `temps_total` INTEGER,
  `ticket_obtenu` INTEGER,
  `bonus_obtenu` INTEGER
);

CREATE TABLE IF NOT EXISTS `UserAnswer` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER,
  `question_id` INTEGER,
  `choix_id` INTEGER,
  `est_correct` INTEGER,
  `temps_reponse` INTEGER,
  `points_obtenus` INTEGER,
  `date` DATETIME
);

CREATE TABLE IF NOT EXISTS `Score` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER,
  `points_total` INTEGER,
  `points_bonus` INTEGER,
  `rang_id` INTEGER
);

CREATE TABLE IF NOT EXISTS `Progress` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER,
  `chapter_id` INTEGER,
  `unit_id` INTEGER,
  `pourcentage` REAL,
  `termine` INTEGER
);

CREATE TABLE IF NOT EXISTS `LotteryTicket` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER,
  `weekly_id` INTEGER,
  `date_obtenue` DATE,
  `bonus` INTEGER
);

CREATE TABLE IF NOT EXISTS `WeeklySeries` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER,
  `count` INTEGER,
  `bonus_tickets` INTEGER,
  `derniere_participation` DATE
);

CREATE TABLE IF NOT EXISTS `Notification` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER,
  `type` TEXT,
  `message` TEXT,
  `lu` INTEGER, -- SQLite utilise INTEGER pour BOOLEAN
  `date` DATETIME
);

-- Création des index pour améliorer les performances
CREATE INDEX IF NOT EXISTS idx_user_rang ON `User` (`rang_id`);
CREATE INDEX IF NOT EXISTS idx_user_email ON `User` (`email`);
CREATE INDEX IF NOT EXISTS idx_unit_chapter ON `Unit` (`chapter_id`);
CREATE INDEX IF NOT EXISTS idx_question_unit ON `Question` (`unit_id`);
CREATE INDEX IF NOT EXISTS idx_choice_question ON `Choice` (`question_id`);
CREATE INDEX IF NOT EXISTS idx_discovery_chapter ON `Discovery` (`chapter_id`);
CREATE INDEX IF NOT EXISTS idx_novelty_chapter ON `Novelty` (`chapter_id`);
CREATE INDEX IF NOT EXISTS idx_event_chapter ON `Event` (`chapter_id`);
CREATE INDEX IF NOT EXISTS idx_reminder_chapter ON `Reminder` (`chapter_id`);
CREATE INDEX IF NOT EXISTS idx_weekly_chapter ON `Weekly` (`chapter_id`);
CREATE INDEX IF NOT EXISTS idx_quiz_instance_user ON `QuizInstance` (`user_id`);
CREATE INDEX IF NOT EXISTS idx_quiz_instance_type ON `QuizInstance` (`quiz_type_id`);
CREATE INDEX IF NOT EXISTS idx_user_quiz_score_instance ON `UserQuizScore` (`quiz_instance_id`);
CREATE INDEX IF NOT EXISTS idx_user_answer_user ON `UserAnswer` (`user_id`);
CREATE INDEX IF NOT EXISTS idx_user_answer_question ON `UserAnswer` (`question_id`);
CREATE INDEX IF NOT EXISTS idx_user_answer_choice ON `UserAnswer` (`choix_id`);
CREATE INDEX IF NOT EXISTS idx_score_user ON `Score` (`user_id`);
CREATE INDEX IF NOT EXISTS idx_progress_user ON `Progress` (`user_id`);
CREATE INDEX IF NOT EXISTS idx_progress_chapter ON `Progress` (`chapter_id`);
CREATE INDEX IF NOT EXISTS idx_progress_unit ON `Progress` (`unit_id`);
CREATE INDEX IF NOT EXISTS idx_lottery_ticket_user ON `LotteryTicket` (`user_id`);
CREATE INDEX IF NOT EXISTS idx_lottery_ticket_weekly ON `LotteryTicket` (`weekly_id`);
CREATE INDEX IF NOT EXISTS idx_weekly_series_user ON `WeeklySeries` (`user_id`);
CREATE INDEX IF NOT EXISTS idx_notification_user ON `Notification` (`user_id`);
