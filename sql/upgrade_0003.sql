ALTER TABLE `compucertificate_certificate` ADD COLUMN `min_valid_from_date` date NULL COMMENT 'Min date the certificate validity starts';
ALTER TABLE `compucertificate_certificate` ADD COLUMN `max_valid_through_date` date NULL COMMENT 'Max date the certificate validity ends';
