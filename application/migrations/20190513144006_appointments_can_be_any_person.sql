-- appointments_can_be_any_person --
SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE `phppos_appointments` DROP FOREIGN KEY `phppos_appointments_ibfk_2`;

ALTER TABLE `phppos_appointments` CHANGE COLUMN `customer_id` `person_id` int(10) NULL DEFAULT NULL,
ADD CONSTRAINT `phppos_appointments_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `phppos_people` (`person_id`);

SET FOREIGN_KEY_CHECKS=1;
