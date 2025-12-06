-- /*******************************************************
-- *
-- * compucertificate_event_attribute
-- *
-- * Column to store event type filters for event certificates
-- *
-- *******************************************************/
ALTER TABLE `compucertificate_event_attribute` ADD COLUMN `event_type_ids` varchar(200) NULL COMMENT 'Comma separated event type ids.';
