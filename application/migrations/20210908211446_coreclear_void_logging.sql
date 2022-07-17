-- coreclear_void_logging --

CREATE TABLE `phppos_processing_return_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `employee_id` int(10) NOT NULL,
  `sale_id` int(10) NULL DEFAULT NULL,
  `orig_voided_processor_transaction_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `voided_processor_transaction_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(23,10) NULL DEFAULT NULL, 
   CONSTRAINT `phppos_processing_return_logs_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_processing_return_logs_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;