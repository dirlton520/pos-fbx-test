-- attach_files_to_people --

CREATE TABLE `phppos_people_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(10) NOT NULL,
  `person_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_people_files_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `phppos_app_files` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
