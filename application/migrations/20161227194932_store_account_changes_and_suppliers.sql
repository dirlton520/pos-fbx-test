-- store_account_changes_and_suppliers --

CREATE TABLE `phppos_store_accounts_paid_sales` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `store_account_payment_sale_id` int(10) DEFAULT NULL,
  `sale_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_store_accounts_sales_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_store_accounts_sales_ibfk_2` FOREIGN KEY (`store_account_payment_sale_id`) REFERENCES `phppos_sales` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `phppos_locations` ADD `net_e_pay_server` TEXT NULL AFTER `emv_merchant_id`;

ALTER TABLE  `phppos_suppliers` ADD  `balance` DECIMAL(23,10) NOT NULL DEFAULT '0.0000000000' AFTER `override_default_tax`;

CREATE TABLE `phppos_receivings_payments` (
  `payment_id` int(10) NOT NULL AUTO_INCREMENT,
  `receiving_id` int(10) NOT NULL,
  `payment_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payment_amount` decimal(23,10) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `receiving_id` (`receiving_id`),
	KEY `payment_date` (`payment_date`),
  CONSTRAINT `phppos_receivings_payments_ibfk_1` FOREIGN KEY (`receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `phppos_receivings` CHANGE `payment_type` `payment_type` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
UPDATE phppos_receivings SET payment_type = REPLACE(payment_type, '&#8209;','<span style="white-space:nowrap;">-</span>');
UPDATE phppos_receivings SET payment_type = REPLACE(payment_type, '&#8209;','<span style="white-space:nowrap;">-</span>');

ALTER table `phppos_receivings` ADD `store_account_payment` int(1) NOT NULL DEFAULT '0';
CREATE TABLE `phppos_supplier_store_accounts` (
  `sno` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `receiving_id` int(11) DEFAULT NULL,
  `transaction_amount` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `balance` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`sno`),
  KEY `phppos_supplier_store_accounts_ibfk_1` (`receiving_id`),
  KEY `phppos_supplier_store_accounts_ibfk_2` (`supplier_id`),
  CONSTRAINT `phppos_supplier_store_accounts_ibfk_1` FOREIGN KEY (`receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`),
  CONSTRAINT `phppos_supplier_store_accounts_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `phppos_suppliers` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_supplier_store_accounts_paid_receivings` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `store_account_payment_receiving_id` int(10) DEFAULT NULL,
  `receiving_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_supplier_store_accounts_paid_receivings_ibfk_1` FOREIGN KEY (`receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`),
  CONSTRAINT `phppos_supplier_store_accounts_paid_receivings_ibfk_2` FOREIGN KEY (`store_account_payment_receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('edit_store_account_balance', 'suppliers', 'suppliers_edit_store_account_balance', 130);
INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'suppliers' and
action_id = 'edit_store_account_balance'
order by module_id, person_id;


INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_store_account_suppliers', 'reports', 'reports_store_account_suppliers', 255);
INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_store_account_suppliers'
order by module_id, person_id;