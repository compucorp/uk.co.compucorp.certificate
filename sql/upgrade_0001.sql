SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civicrm_compu_certificate_event_attribute`;

SET FOREIGN_KEY_CHECKS=1;

-- /*******************************************************
-- *
-- * civicrm_compu_certificate_event_attribute
-- *
-- * Table to store attributes peculiar to CompuCertificate of type event 
-- *
-- *******************************************************/
CREATE TABLE `civicrm_compu_certificate_event_attribute` (
  `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique CompuCertificateEventAttribute ID',
  `certificate_id` int unsigned    COMMENT 'FK to CompuCertificate',
  `participant_type_id` int unsigned    COMMENT 'Particiapnt Type ID',
  PRIMARY KEY (`id`),          
  CONSTRAINT FK_civicrm_compu_certificate_event_attribute_certificate_id FOREIGN KEY (`certificate_id`) REFERENCES `compucertificate_certificate`(`id`) ON DELETE CASCADE  
)  ENGINE=InnoDB  ;