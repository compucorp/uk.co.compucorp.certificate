-- /*******************************************************
-- *
-- * Clean up the existing tables
-- *
-- *******************************************************/

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civicrm_compu_certificate_template_image_format`;
DROP TABLE IF EXISTS `compucertificate_certificate_status`;
DROP TABLE IF EXISTS `compucertificate_event_attribute`;
DROP TABLE IF EXISTS `compucertificate_certificate_entity_type`;
DROP TABLE IF EXISTS `compucertificate_certificate`;

SET FOREIGN_KEY_CHECKS=1;