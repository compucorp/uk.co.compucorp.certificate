-- /*******************************************************
-- *
-- * compucertificate_event_attribute
-- *
-- * Table to store attributes peculiar to CompuCertificate of type event 
-- *
-- *******************************************************/
ALTER TABLE `compucertificate_certificate` ADD COLUMN `download_format` INT UNSIGNED NULL COMMENT 'Predefined CompuCertificate download format (1 - PDF, 2 - IMAGE)' AFTER `template_id`;
ALTER TABLE `compucertificate_certificate` ADD COLUMN `start_date` date NULL COMMENT 'Date the certificate validity starts' AFTER `download_format`;
ALTER TABLE `compucertificate_certificate` ADD COLUMN `end_date` date NULL COMMENT 'Date the certificate validity ends' AFTER `start_date`;
