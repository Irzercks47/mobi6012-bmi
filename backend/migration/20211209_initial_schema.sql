CREATE DATABASE IF NOT EXISTS `bmi_app`;
USE `bmi_app`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `timestamp_created` datetime NOT NULL DEFAULT (UTC_TIMESTAMP),
  `timestamp_updated` datetime NOT NULL DEFAULT (UTC_TIMESTAMP),
  `verification_hash` char(64),
  `timestamp_verified` datetime,
  `timestamp_verification_deadline` datetime,
  `password_recovery_hash` char(64),
  `password_recovery_deadline` datetime
);

CREATE TABLE IF NOT EXISTS `reports` (
  `id` bigint unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `height` smallint unsigned NOT NULL,
  `weight` smallint unsigned NOT NULL,
  `timestamp_created` datetime NOT NULL DEFAULT (UTC_TIMESTAMP),
  `timestamp_updated` datetime NOT NULL DEFAULT (UTC_TIMESTAMP),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);
