-- /*******************************************************
-- *
-- * Clean up the existing tables
-- *
-- *******************************************************/

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `compucertificate_certificate_status`;
DROP TABLE IF EXISTS `compucertificate_event_attribute`;
DROP TABLE IF EXISTS `compucertificate_certificate_entity_type`;
DROP TABLE IF EXISTS `compucertificate_certificate`;

SET FOREIGN_KEY_CHECKS=1;
-- /*******************************************************
-- *
-- * Create new tables
-- *
-- *******************************************************/

-- /*******************************************************
-- *
-- * compucertificate_certificate
-- *
-- * CompuCertificate table
-- *
-- *******************************************************/
CREATE TABLE `compucertificate_certificate` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique CompuCertificate ID',
     `name` varchar(255) NOT NULL   COMMENT 'Certificate name',
     `entity` int unsigned NOT NULL   COMMENT 'Predefined CompuCertificate Entity ID (1 - CASE, 2 - MEMBERSHIP, 3 - EVENT) ',
     `template_id` int unsigned NOT NULL   COMMENT 'FK to message template' 
,
        PRIMARY KEY (`id`)
 
 
,          CONSTRAINT FK_compucertificate_certificate_template_id FOREIGN KEY (`template_id`) REFERENCES `civicrm_msg_template`(`id`) ON DELETE CASCADE  
)  ENGINE=InnoDB  ;

-- /*******************************************************
-- *
-- * compucertificate_certificate_entity_type
-- *
-- * CompuCertificate Entity Type table that will morph to the appropraite type depending on the entity
-- *
-- *******************************************************/
CREATE TABLE `compucertificate_certificate_entity_type` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique CompuCertificateEntityType ID',
     `certificate_id` int unsigned    COMMENT 'FK to CompuCertificate',
     `entity_type_id` int unsigned    COMMENT 'Entity type ID' 
,
        PRIMARY KEY (`id`)
 
 
,          CONSTRAINT FK_compucertificate_certificate_entity_type_certificate_id FOREIGN KEY (`certificate_id`) REFERENCES `compucertificate_certificate`(`id`) ON DELETE CASCADE  
)  ENGINE=InnoDB  ;

-- /*******************************************************
-- *
-- * compucertificate_event_attribute
-- *
-- * Table to store attributes peculiar to CompuCertificate of type event 
-- *
-- *******************************************************/
CREATE TABLE `compucertificate_event_attribute` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique CompuCertificateEventAttribute ID',
     `certificate_id` int unsigned    COMMENT 'FK to CompuCertificate',
     `participant_type_id` int unsigned    COMMENT 'Particiapnt Type ID' 
,
        PRIMARY KEY (`id`)
 
 
,          CONSTRAINT FK_compucertificate_event_attribute_certificate_id FOREIGN KEY (`certificate_id`) REFERENCES `compucertificate_certificate`(`id`) ON DELETE CASCADE  
)  ENGINE=InnoDB  ;

-- /*******************************************************
-- *
-- * compucertificate_certificate_status
-- *
-- * CompuCertificate Entity Status table that will morph to the appropraite status depending on the entity
-- *
-- *******************************************************/
CREATE TABLE `compucertificate_certificate_status` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique CompuCertificateStatus ID',
     `certificate_id` int unsigned    COMMENT 'FK to CompuCertificate',
     `status_id` int unsigned    COMMENT 'Entity status ID' 
,
        PRIMARY KEY (`id`)
 
 
,          CONSTRAINT FK_compucertificate_certificate_status_certificate_id FOREIGN KEY (`certificate_id`) REFERENCES `compucertificate_certificate`(`id`) ON DELETE CASCADE  
)  ENGINE=InnoDB  ;

 