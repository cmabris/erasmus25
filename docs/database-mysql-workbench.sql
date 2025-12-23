-- =====================================================
-- Script SQL para MySQL Workbench - Erasmus25 Database
-- =====================================================
-- Este script crea todas las tablas y relaciones de la base de datos
-- Para generar el diagrama EER en MySQL Workbench:
-- 1. Abre MySQL Workbench
-- 2. File > Import > Run SQL Script
-- 3. Selecciona este archivo
-- 4. Ejecuta el script
-- 5. Database > Reverse Engineer (Ctrl+R)
-- 6. Selecciona la base de datos y todas las tablas
-- 7. MySQL Workbench generará automáticamente el diagrama EER
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Tabla: users
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `two_factor_secret` text,
  `two_factor_recovery_codes` text,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: programs
DROP TABLE IF EXISTS `programs`;
CREATE TABLE `programs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `programs_code_unique` (`code`),
  UNIQUE KEY `programs_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: academic_years
DROP TABLE IF EXISTS `academic_years`;
CREATE TABLE `academic_years` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `year` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `academic_years_year_unique` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: calls
DROP TABLE IF EXISTS `calls`;
CREATE TABLE `calls` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `program_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `type` enum('alumnado','personal') NOT NULL,
  `modality` enum('corta','larga') NOT NULL,
  `number_of_places` int NOT NULL,
  `destinations` json DEFAULT NULL,
  `estimated_start_date` date DEFAULT NULL,
  `estimated_end_date` date DEFAULT NULL,
  `requirements` text,
  `documentation` text,
  `selection_criteria` text,
  `scoring_table` json DEFAULT NULL,
  `status` enum('borrador','abierta','cerrada','en_baremacion','resuelta','archivada') NOT NULL DEFAULT 'borrador',
  `published_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `calls_slug_unique` (`slug`),
  KEY `calls_program_id_foreign` (`program_id`),
  KEY `calls_academic_year_id_foreign` (`academic_year_id`),
  KEY `calls_created_by_foreign` (`created_by`),
  KEY `calls_updated_by_foreign` (`updated_by`),
  KEY `calls_program_id_academic_year_id_status_index` (`program_id`,`academic_year_id`,`status`),
  KEY `calls_status_published_at_index` (`status`,`published_at`),
  CONSTRAINT `calls_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `calls_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `calls_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `calls_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: call_phases
DROP TABLE IF EXISTS `call_phases`;
CREATE TABLE `call_phases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `call_id` bigint unsigned NOT NULL,
  `phase_type` enum('publicacion','solicitudes','provisional','alegaciones','definitivo','renuncias','lista_espera') NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT '0',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `call_phases_call_id_foreign` (`call_id`),
  KEY `call_phases_call_id_is_current_index` (`call_id`,`is_current`),
  CONSTRAINT `call_phases_call_id_foreign` FOREIGN KEY (`call_id`) REFERENCES `calls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: call_applications
DROP TABLE IF EXISTS `call_applications`;
CREATE TABLE `call_applications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `call_id` bigint unsigned NOT NULL,
  `applicant_name` varchar(255) NOT NULL,
  `applicant_email` varchar(255) NOT NULL,
  `applicant_phone` varchar(255) DEFAULT NULL,
  `status` enum('pendiente','admitida','rechazada','renunciada') NOT NULL DEFAULT 'pendiente',
  `score` decimal(5,2) DEFAULT NULL,
  `position` int DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `call_applications_call_id_foreign` (`call_id`),
  KEY `call_applications_call_id_status_index` (`call_id`,`status`),
  KEY `call_applications_call_id_position_index` (`call_id`,`position`),
  CONSTRAINT `call_applications_call_id_foreign` FOREIGN KEY (`call_id`) REFERENCES `calls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: resolutions
DROP TABLE IF EXISTS `resolutions`;
CREATE TABLE `resolutions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `call_id` bigint unsigned NOT NULL,
  `call_phase_id` bigint unsigned NOT NULL,
  `type` enum('provisional','definitivo','alegaciones') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `evaluation_procedure` text,
  `official_date` date NOT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resolutions_call_id_foreign` (`call_id`),
  KEY `resolutions_call_phase_id_foreign` (`call_phase_id`),
  KEY `resolutions_created_by_foreign` (`created_by`),
  KEY `resolutions_call_id_type_index` (`call_id`,`type`),
  CONSTRAINT `resolutions_call_id_foreign` FOREIGN KEY (`call_id`) REFERENCES `calls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `resolutions_call_phase_id_foreign` FOREIGN KEY (`call_phase_id`) REFERENCES `call_phases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `resolutions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: document_categories
DROP TABLE IF EXISTS `document_categories`;
CREATE TABLE `document_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: documents
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `program_id` bigint unsigned DEFAULT NULL,
  `academic_year_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `document_type` enum('convocatoria','modelo','seguro','consentimiento','guia','faq','otro') NOT NULL,
  `version` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `download_count` int NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `documents_slug_unique` (`slug`),
  KEY `documents_category_id_foreign` (`category_id`),
  KEY `documents_program_id_foreign` (`program_id`),
  KEY `documents_academic_year_id_foreign` (`academic_year_id`),
  KEY `documents_created_by_foreign` (`created_by`),
  KEY `documents_updated_by_foreign` (`updated_by`),
  KEY `documents_category_id_program_id_is_active_index` (`category_id`,`program_id`,`is_active`),
  CONSTRAINT `documents_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `document_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `documents_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL,
  CONSTRAINT `documents_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `documents_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: news_posts
DROP TABLE IF EXISTS `news_posts`;
CREATE TABLE `news_posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `program_id` bigint unsigned DEFAULT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text,
  `content` longtext NOT NULL,
  `country` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `host_entity` varchar(255) DEFAULT NULL,
  `mobility_type` enum('alumnado','personal') DEFAULT NULL,
  `mobility_category` enum('FCT','job_shadowing','intercambio','curso','otro') DEFAULT NULL,
  `status` enum('borrador','en_revision','publicado','archivado') NOT NULL DEFAULT 'borrador',
  `published_at` timestamp NULL DEFAULT NULL,
  `author_id` bigint unsigned DEFAULT NULL,
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `news_posts_slug_unique` (`slug`),
  KEY `news_posts_program_id_foreign` (`program_id`),
  KEY `news_posts_academic_year_id_foreign` (`academic_year_id`),
  KEY `news_posts_author_id_foreign` (`author_id`),
  KEY `news_posts_reviewed_by_foreign` (`reviewed_by`),
  KEY `news_posts_program_id_status_published_at_index` (`program_id`,`status`,`published_at`),
  KEY `news_posts_academic_year_id_status_index` (`academic_year_id`,`status`),
  CONSTRAINT `news_posts_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `news_posts_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `news_posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `news_posts_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: news_tags
DROP TABLE IF EXISTS `news_tags`;
CREATE TABLE `news_tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `news_tags_name_unique` (`name`),
  UNIQUE KEY `news_tags_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: news_post_tag (many-to-many)
DROP TABLE IF EXISTS `news_post_tag`;
CREATE TABLE `news_post_tag` (
  `news_post_id` bigint unsigned NOT NULL,
  `news_tag_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`news_post_id`,`news_tag_id`),
  KEY `news_post_tag_news_tag_id_foreign` (`news_tag_id`),
  CONSTRAINT `news_post_tag_news_post_id_foreign` FOREIGN KEY (`news_post_id`) REFERENCES `news_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `news_post_tag_news_tag_id_foreign` FOREIGN KEY (`news_tag_id`) REFERENCES `news_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: erasmus_events
DROP TABLE IF EXISTS `erasmus_events`;
CREATE TABLE `erasmus_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `program_id` bigint unsigned DEFAULT NULL,
  `call_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `event_type` enum('apertura','cierre','entrevista','publicacion_provisional','publicacion_definitivo','reunion_informativa','otro') NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `erasmus_events_program_id_foreign` (`program_id`),
  KEY `erasmus_events_call_id_foreign` (`call_id`),
  KEY `erasmus_events_created_by_foreign` (`created_by`),
  KEY `erasmus_events_program_id_start_date_index` (`program_id`,`start_date`),
  KEY `erasmus_events_call_id_start_date_index` (`call_id`,`start_date`),
  KEY `erasmus_events_is_public_start_date_index` (`is_public`,`start_date`),
  CONSTRAINT `erasmus_events_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `erasmus_events_call_id_foreign` FOREIGN KEY (`call_id`) REFERENCES `calls` (`id`) ON DELETE SET NULL,
  CONSTRAINT `erasmus_events_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: media (Laravel Media Library)
DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `collection_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `disk` varchar(255) NOT NULL,
  `conversions_disk` varchar(255) DEFAULT NULL,
  `size` bigint unsigned NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `media_order_column_index` (`order_column`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: media_consents
DROP TABLE IF EXISTS `media_consents`;
CREATE TABLE `media_consents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `media_id` bigint unsigned NOT NULL,
  `consent_type` enum('imagen','video','audio') NOT NULL,
  `person_name` varchar(255) DEFAULT NULL,
  `person_email` varchar(255) DEFAULT NULL,
  `consent_given` tinyint(1) NOT NULL,
  `consent_date` date NOT NULL,
  `consent_document_id` bigint unsigned DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `media_consents_media_id_index` (`media_id`),
  KEY `media_consents_consent_type_consent_given_index` (`consent_type`,`consent_given`),
  KEY `media_consents_consent_document_id_foreign` (`consent_document_id`),
  CONSTRAINT `media_consents_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE,
  CONSTRAINT `media_consents_consent_document_id_foreign` FOREIGN KEY (`consent_document_id`) REFERENCES `documents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: languages
DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(2) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `languages_code_unique` (`code`),
  KEY `languages_is_default_is_active_index` (`is_default`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: translations
DROP TABLE IF EXISTS `translations`;
CREATE TABLE `translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `translatable_type` varchar(255) NOT NULL,
  `translatable_id` bigint unsigned NOT NULL,
  `language_id` bigint unsigned NOT NULL,
  `field` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `translation_unique` (`translatable_type`,`translatable_id`,`language_id`,`field`),
  KEY `translations_language_id_foreign` (`language_id`),
  KEY `translations_translatable_type_translatable_id_index` (`translatable_type`,`translatable_id`),
  CONSTRAINT `translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: settings
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text,
  `type` enum('string','integer','boolean','json') NOT NULL DEFAULT 'string',
  `group` enum('general','email','rgpd','media','seo') NOT NULL DEFAULT 'general',
  `description` text,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`),
  KEY `settings_updated_by_foreign` (`updated_by`),
  KEY `settings_group_key_index` (`group`,`key`),
  CONSTRAINT `settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: newsletter_subscriptions
DROP TABLE IF EXISTS `newsletter_subscriptions`;
CREATE TABLE `newsletter_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `programs` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `subscribed_at` timestamp NOT NULL,
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `newsletter_subscriptions_email_unique` (`email`),
  KEY `newsletter_subscriptions_email_is_active_index` (`email`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: notifications
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` enum('convocatoria','resolucion','noticia','revision','sistema') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  KEY `notifications_user_id_is_read_index` (`user_id`,`is_read`),
  KEY `notifications_type_created_at_index` (`type`,`created_at`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: audit_logs
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` enum('create','update','delete','publish','archive','restore') NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `changes` json DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `audit_logs_model_type_model_id_index` (`model_type`,`model_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: sessions
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`),
  CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tablas de Laravel Permission (Spatie)
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tablas del sistema Laravel (cache, jobs, etc.)
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;


