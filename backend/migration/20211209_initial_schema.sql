CREATE DATABASE `bmi_app` IF NOT EXISTS;
USE `bmi_app`;

CREATE TABLE `users` IF NOT EXISTS (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `timestamp_created` datetime NOT NULL DEFAULT (UTC_TIMESTAMP),
  `timestamp_updated` datetime NOT NULL DEFAULT (UTC_TIMESTAMP),
  `verification_hash` char(64),
  `timestamp_verified` datetime,
  `timestamp_verification_deadline` datetime,
  `password_recovery_hash` char(64),
  `password_recovery_deadline` datetime
);

CREATE TABLE `reports` IF NOT EXISTS (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `height` smallint unsigned NOT NULL,
  `weight` smallint unsigned NOT NULL,
  `timestamp_created` datetime NOT NULL DEFAULT (UTC_TIMESTAMP),
  `timestamp_updated` datetime NOT NULL DEFAULT (UTC_TIMESTAMP),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);
