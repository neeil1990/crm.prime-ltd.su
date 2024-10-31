CREATE TABLE IF NOT EXISTS `pin_ticket_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_comment_id` int(11) NOT NULL,
  `pinned_by` int(11) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ; --#

CREATE TABLE IF NOT EXISTS `task_personal_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `created_by` int NOT NULL,
  `text` mediumtext NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`task_id`),
  KEY (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ; --#
