-- بعد USE graduation_projects; — admin / كلمة المرور: ١٢٣٤ (الأرقام العربية في لوحة المفاتيح العربية)

UPDATE `admins`
SET `password` = '$2y$12$A0WscPn4FxKtYh380dY.IOcARz0fr4DV0U8a4U6/DWjuu/JK1pt7K'
WHERE `username` = 'admin'
LIMIT 1;
