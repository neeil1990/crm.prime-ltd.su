ALTER TABLE `rise_ticket_comments` ADD `read_at` DATETIME NULL DEFAULT NULL AFTER `is_read`; --#
ALTER TABLE `rise_ticket_comments` ADD `sent_at` DATETIME NULL DEFAULT NULL AFTER `read_at`; --#
ALTER TABLE `rise_ticket_comments` ADD `sent_to` MEDIUMTEXT NULL DEFAULT NULL AFTER `sent_at`; --#
