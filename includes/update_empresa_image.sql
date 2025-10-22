-- Add image field to empresa table
ALTER TABLE `empresa` ADD COLUMN `imagen` VARCHAR(255) DEFAULT NULL AFTER `objetivos`;
