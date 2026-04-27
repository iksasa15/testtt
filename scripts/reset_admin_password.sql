-- نفّذ على قاعدة graduation_projects بعد USE graduation_projects;
-- بعد التنفيذ: admin / 1234

UPDATE `admins`
SET `password` = '$2y$12$50YkvymOJHDtbqSSwi950OnOB.gCwtA7TjjhPc0.duG7vQcSak5SS'
WHERE `username` = 'admin'
LIMIT 1;
