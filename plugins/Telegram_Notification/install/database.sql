CREATE TABLE IF NOT EXISTS `telegram_integration_settings` (
  `setting_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `setting_value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'app',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #

INSERT INTO `telegram_integration_settings` (`setting_name`, `setting_value`, `deleted`) VALUES ('telegram_notification_item_purchase_code', 'Telegram_Notification-ITEM-PURCHASE-CODE', 0); #

CREATE TABLE IF NOT EXISTS `telegram_notification_settings`(
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `event` VARCHAR(250) NOT NULL,
    `enable_telegram` INT(1) NOT NULL DEFAULT '0',
    `deleted` INT(1) NOT NULL DEFAULT '0',
    PRIMARY KEY(`id`),
    KEY `event`(`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT = 1; #

