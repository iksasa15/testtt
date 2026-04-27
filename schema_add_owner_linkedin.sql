-- شغّل هذا المرة واحدة على قاعدة موجودة:
-- mysql -u root -p graduation_projects < schema_add_owner_linkedin.sql

ALTER TABLE `projects`
  ADD COLUMN `owner_linkedin` varchar(512) DEFAULT NULL AFTER `tech_stack`;
