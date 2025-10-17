ALTER TABLE `compucertificate_certificate`
  ADD COLUMN `event_type_ids` TEXT NULL COMMENT 'Serialized list of event type IDs associated with an Event certificate';
