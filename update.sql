CREATE TABLE `locations_sections` (
  `id` int(11) NOT NULL,
  `locations_id` int(11) NOT NULL,
  `type` varchar(30) NOT NULL,
  `value` int(11) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '99999',
  FOREIGN KEY (`locations_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE
) ENGINE='InnoDB';

ALTER TABLE `locations_sections`
CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

ALTER TABLE `locations_translations`
DROP `text`;

DROP TABLE IF EXISTS `locations_texts`;
CREATE TABLE `locations_texts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `locations_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `locations_id` (`locations_id`),
  CONSTRAINT `locations_texts_ibfk_1` FOREIGN KEY (`locations_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

TRUNCATE `locations_texts`;

DROP TABLE IF EXISTS `locations_texts_translations`;
CREATE TABLE `locations_texts_translations` (
  `locations_texts_id` int(11) NOT NULL,
  `locale` varchar(2) COLLATE utf8_czech_ci NOT NULL DEFAULT 'cs',
  `text` text COLLATE utf8_czech_ci NOT NULL,
  KEY `locations_texts_id` (`locations_texts_id`),
  CONSTRAINT `locations_texts_translations_ibfk_2` FOREIGN KEY (`locations_texts_id`) REFERENCES `locations_texts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

TRUNCATE `locations_texts_translations`;

DROP TABLE IF EXISTS `locations_images`;
CREATE TABLE `locations_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `locations_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `locations_id` (`locations_id`),
  CONSTRAINT `locations_images_ibfk_1` FOREIGN KEY (`locations_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

TRUNCATE `locations_images`;

DROP TABLE IF EXISTS `locations_images_files`;
CREATE TABLE `locations_images_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `locations_images_id` int(11) NOT NULL,
  `filename` varchar(90) COLLATE utf8_czech_ci NOT NULL,
  `order` int(11) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`id`),
  KEY `locations_images_id` (`locations_images_id`),
  CONSTRAINT `locations_images_files_ibfk_1` FOREIGN KEY (`locations_images_id`) REFERENCES `locations_images` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

TRUNCATE `locations_images_files`;

DROP TABLE IF EXISTS `locations_images_files_translations`;
CREATE TABLE `locations_images_files_translations` (
  `locations_images_files_id` int(11) NOT NULL,
  `locale` varchar(2) COLLATE utf8_czech_ci NOT NULL DEFAULT 'cs',
  `text` text COLLATE utf8_czech_ci NOT NULL,
  KEY `locations_images_files_id` (`locations_images_files_id`),
  CONSTRAINT `locations_images_files_translations_ibfk_1` FOREIGN KEY (`locations_images_files_id`) REFERENCES `locations_images_files` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

TRUNCATE `locations_images_files_translations`;


ALTER TABLE `locations`
ADD `geo_json` text NOT NULL AFTER `show`;