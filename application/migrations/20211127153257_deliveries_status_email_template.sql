-- deliveries_status_email_template --
CREATE TABLE `phppos_delivery_email_templates`(
	`id` int(11) NOT NULL  auto_increment , 
	`status_id` int(11) NOT NULL  , 
	`content` longtext COLLATE utf8_unicode_ci NULL  , 
	PRIMARY KEY (`id`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
