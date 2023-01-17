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

-- /*******************************************************
-- *
-- * compu_certificate_template_image_format
-- *
-- * Table to store image format linked to a message template
-- *
-- *******************************************************/
CREATE TABLE `compu_certificate_template_image_format` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique CompuCertificateTemplateImageFormat ID',
  `template_id` int unsigned NOT NULL COMMENT 'FK to message template',
  `image_format_id` int unsigned NULL COMMENT 'FK to certificate image format option group',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_compu_certificate_template_image_format_template_id FOREIGN KEY (`template_id`) REFERENCES `civicrm_msg_template`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;
