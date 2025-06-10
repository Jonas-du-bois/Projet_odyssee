-- Schéma PostgreSQL pour Breitling League
-- Optimisé pour déploiement Heroku
-- Généré le 10 juin 2025

-- Extension pour UUID si nécessaire
-- CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

CREATE TABLE ranks (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  level INTEGER NOT NULL,
  minimum_points INTEGER NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE users (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  rank_id BIGINT NULL REFERENCES ranks(id) ON DELETE SET NULL,
  registration_date DATE NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  email_verified_at TIMESTAMP NULL DEFAULT NULL,
  remember_token VARCHAR(100) NULL
);

CREATE TABLE chapters (
  id BIGSERIAL PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  theory_content TEXT NULL,
  is_active BOOLEAN NOT NULL DEFAULT true,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE units (
  id BIGSERIAL PRIMARY KEY,
  chapter_id BIGINT NOT NULL REFERENCES chapters(id) ON DELETE CASCADE,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  theory_html TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Table Questions avec système polymorphique
CREATE TABLE questions (
  id BIGSERIAL PRIMARY KEY,
  quizable_type VARCHAR(255) NULL,
  quizable_id BIGINT NULL,
  question_text TEXT NULL,
  options JSON NULL,
  correct_answer VARCHAR(255) NULL,
  timer_seconds INTEGER NULL,
  type VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Index pour la relation polymorphique des questions
CREATE INDEX questions_quizable_type_quizable_id_idx ON questions(quizable_type, quizable_id);

CREATE TABLE choices (
  id BIGSERIAL PRIMARY KEY,
  question_id BIGINT NOT NULL REFERENCES questions(id) ON DELETE CASCADE,
  text VARCHAR(255) NOT NULL,
  is_correct BOOLEAN NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE discoveries (
  id BIGSERIAL PRIMARY KEY,
  chapter_id BIGINT NOT NULL REFERENCES chapters(id) ON DELETE CASCADE,
  available_date DATE NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE novelties (
  id BIGSERIAL PRIMARY KEY,
  chapter_id BIGINT NOT NULL REFERENCES chapters(id) ON DELETE CASCADE,
  publication_date DATE NOT NULL,
  initial_bonus BOOLEAN NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE events (
  id BIGSERIAL PRIMARY KEY,
  theme VARCHAR(255) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE event_units (
  id BIGSERIAL PRIMARY KEY,
  event_id BIGINT NOT NULL REFERENCES events(id) ON DELETE CASCADE,
  unit_id BIGINT NOT NULL REFERENCES units(id) ON DELETE CASCADE,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE reminders (
  id BIGSERIAL PRIMARY KEY,
  chapter_id BIGINT NOT NULL REFERENCES chapters(id) ON DELETE CASCADE,
  number_questions INTEGER NOT NULL,
  deadline_date DATE NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE weeklies (
  id BIGSERIAL PRIMARY KEY,
  chapter_id BIGINT NOT NULL REFERENCES chapters(id) ON DELETE CASCADE,
  week_start DATE NOT NULL,
  number_questions INTEGER NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE last_chances (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Table QuizTypes avec morph_type pour le système polymorphique
CREATE TABLE quiz_types (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  morph_type VARCHAR(255) NULL,
  base_points INTEGER NOT NULL,
  speed_bonus BOOLEAN NOT NULL,
  gives_ticket BOOLEAN NOT NULL,
  bonus_multiplier INTEGER NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Type ENUM pour le statut des quiz instances
CREATE TYPE quiz_status AS ENUM ('active', 'completed', 'abandoned');

-- Table QuizInstances avec système polymorphique
CREATE TABLE quiz_instances (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  quiz_type_id BIGINT NOT NULL REFERENCES quiz_types(id) ON DELETE CASCADE,
  quizable_type VARCHAR(255) NULL,
  quizable_id BIGINT NULL,
  quiz_mode VARCHAR(255) NOT NULL DEFAULT 'quiz',
  launch_date TIMESTAMP NOT NULL,
  speed_bonus BOOLEAN NOT NULL DEFAULT false,
  status quiz_status NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Index pour la relation polymorphique des quiz instances
CREATE INDEX quiz_instances_quizable_type_quizable_id_idx ON quiz_instances(quizable_type, quizable_id);

CREATE TABLE user_quiz_scores (
  id BIGSERIAL PRIMARY KEY,
  quiz_instance_id BIGINT NOT NULL REFERENCES quiz_instances(id) ON DELETE CASCADE,
  total_points INTEGER NOT NULL,
  total_time INTEGER NOT NULL,
  ticket_obtained BOOLEAN NOT NULL,
  bonus_obtained BOOLEAN NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Table UserAnswers avec quiz_instance_id
CREATE TABLE user_answers (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  quiz_instance_id BIGINT NULL REFERENCES quiz_instances(id) ON DELETE CASCADE,
  question_id BIGINT NOT NULL REFERENCES questions(id) ON DELETE CASCADE,
  choice_id BIGINT NULL REFERENCES choices(id) ON DELETE SET NULL,
  is_correct BOOLEAN NOT NULL,
  response_time INTEGER NOT NULL,
  points_obtained INTEGER NOT NULL,
  date TIMESTAMP NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE scores (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  total_points INTEGER NOT NULL,
  bonus_points INTEGER NOT NULL,
  rank_id BIGINT NOT NULL REFERENCES ranks(id) ON DELETE RESTRICT,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE progress (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  chapter_id BIGINT NOT NULL REFERENCES chapters(id) ON DELETE CASCADE,
  unit_id BIGINT NULL REFERENCES units(id) ON DELETE SET NULL,
  percentage REAL NOT NULL,
  completed BOOLEAN NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE lottery_tickets (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  weekly_id BIGINT NOT NULL REFERENCES weeklies(id) ON DELETE CASCADE,
  obtained_date DATE NOT NULL,
  bonus BOOLEAN NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE weekly_series (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  count INTEGER NOT NULL,
  bonus_tickets INTEGER NOT NULL,
  last_participation DATE NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE notifications (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  type VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  read BOOLEAN NOT NULL,
  date TIMESTAMP NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Tables Laravel par défaut adaptées pour PostgreSQL
CREATE TABLE cache (
  key VARCHAR(255) PRIMARY KEY,
  value TEXT NOT NULL,
  expiration INTEGER NOT NULL
);

CREATE TABLE cache_locks (
  key VARCHAR(255) PRIMARY KEY,
  owner VARCHAR(255) NOT NULL,
  expiration INTEGER NOT NULL
);

CREATE TABLE jobs (
  id BIGSERIAL PRIMARY KEY,
  queue VARCHAR(255) NOT NULL,
  payload TEXT NOT NULL,
  attempts SMALLINT NOT NULL,
  reserved_at INTEGER NULL,
  available_at INTEGER NOT NULL,
  created_at INTEGER NOT NULL
);

CREATE INDEX jobs_queue_idx ON jobs(queue);

CREATE TABLE job_batches (
  id VARCHAR(255) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  total_jobs INTEGER NOT NULL,
  pending_jobs INTEGER NOT NULL,
  failed_jobs INTEGER NOT NULL,
  failed_job_ids TEXT NOT NULL,
  options TEXT NULL,
  cancelled_at INTEGER NULL,
  created_at INTEGER NOT NULL,
  finished_at INTEGER NULL
);

CREATE TABLE failed_jobs (
  id BIGSERIAL PRIMARY KEY,
  uuid VARCHAR(255) NOT NULL UNIQUE,
  connection TEXT NOT NULL,
  queue TEXT NOT NULL,
  payload TEXT NOT NULL,
  exception TEXT NOT NULL,
  failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE personal_access_tokens (
  id BIGSERIAL PRIMARY KEY,
  tokenable_type VARCHAR(255) NOT NULL,
  tokenable_id BIGINT NOT NULL,
  name VARCHAR(255) NOT NULL,
  token VARCHAR(64) NOT NULL UNIQUE,
  abilities TEXT NULL,
  last_used_at TIMESTAMP NULL,
  expires_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_idx ON personal_access_tokens(tokenable_type, tokenable_id);

-- Index supplémentaires pour les performances
CREATE INDEX users_email_idx ON users(email);
CREATE INDEX users_rank_id_idx ON users(rank_id);
CREATE INDEX chapters_is_active_idx ON chapters(is_active);
CREATE INDEX units_chapter_id_idx ON units(chapter_id);
CREATE INDEX quiz_instances_user_id_idx ON quiz_instances(user_id);
CREATE INDEX quiz_instances_status_idx ON quiz_instances(status);
CREATE INDEX user_answers_user_id_idx ON user_answers(user_id);
CREATE INDEX user_answers_quiz_instance_id_idx ON user_answers(quiz_instance_id);
CREATE INDEX progress_user_id_idx ON progress(user_id);
CREATE INDEX notifications_user_id_read_idx ON notifications(user_id, read);
