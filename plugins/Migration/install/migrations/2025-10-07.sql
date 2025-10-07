ALTER TABLE `rise_ticket_comments` ADD `is_send` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_note`; --#
ALTER TABLE `rise_ticket_comments` ADD `is_read` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_send`; --#
