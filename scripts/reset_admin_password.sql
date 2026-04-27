-- نفّذ مرة واحدة على قاعدة graduation_projects (مثلاً من mysql بعد railway connect MySQL):
--   USE graduation_projects;
--   SOURCE .../reset_admin_password.sql;
--
-- بعد التنفيذ: اسم المستخدم admin — كلمة المرور Admin123!
-- غيّرها فوراً في الإنتاج.

UPDATE `admins`
SET `password` = '$2y$12$SyTjOxDMW13xWT.S4.AqWuYRiqcMWup4TcQ3zNiI4/Vx0IxtGTnje'
WHERE `username` = 'admin'
LIMIT 1;
