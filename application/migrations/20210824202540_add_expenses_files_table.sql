-- add_expenses_files_table --
CREATE TABLE `phppos_expenses_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `expense_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_expenses_files_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `phppos_app_files` (`file_id`),
CONSTRAINT `phppos_expenses_files_ibfk_2` FOREIGN KEY (`expense_id`) REFERENCES `phppos_expenses` (`id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `phppos_expenses_files` ADD INDEX(`file_id`);
ALTER TABLE `phppos_expenses_files` ADD INDEX(`expense_id`);
