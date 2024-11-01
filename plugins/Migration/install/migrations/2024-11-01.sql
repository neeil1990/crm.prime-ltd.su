ALTER TABLE `rise_labels`
CHANGE `context` `context` ENUM('event','invoice','note','project','task','ticket','to_do','subscription','client','private_task')
CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL DEFAULT NULL; --#

ALTER TABLE `rise_tasks` ADD `private_labels` TEXT NULL DEFAULT NULL AFTER `labels`; --#
