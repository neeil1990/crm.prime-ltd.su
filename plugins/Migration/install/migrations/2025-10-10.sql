CREATE TABLE `crm`.`rise_ticket_mails` (`id` INT NOT NULL AUTO_INCREMENT , `ticket_comment_id` INT NOT NULL , `from_user_id` INT NOT NULL , `to_user_id` INT NOT NULL , `created_at` DATETIME NOT NULL , `read_at` DATETIME NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB; --#
ALTER TABLE `rise_ticket_comments` DROP `is_send`, DROP `is_read`, DROP `read_at`, DROP `sent_at`, DROP `sent_to`; --#
