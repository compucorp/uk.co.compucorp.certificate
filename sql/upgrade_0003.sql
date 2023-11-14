-- /*******************************************************
-- *
-- * compucertificate_certificate
-- *
-- * Column to store CompuCertificate download type
-- *
-- *******************************************************/
ALTER TABLE `compucertificate_certificate` ADD COLUMN `download_type` int unsigned NULL DEFAULT 1 COMMENT 'Predefined CompuCertificate download type (1 - MESSAGE TEMPLATE, 2 - FILE)';
ALTER TABLE `compucertificate_certificate` CHANGE `template_id` `template_id` INT(10) UNSIGNED NULL COMMENT 'FK to message template';

-- /*******************************************************
-- *
-- * compucertificate_certificate
-- *
-- * Column to store CompuCertificate valid date
-- *
-- *******************************************************/
ALTER TABLE `compucertificate_certificate` ADD COLUMN `min_valid_from_date` date NULL COMMENT 'Min date the certificate validity starts';
ALTER TABLE `compucertificate_certificate` ADD COLUMN `max_valid_through_date` date NULL COMMENT 'Max date the certificate validity ends';
