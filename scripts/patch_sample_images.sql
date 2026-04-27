-- بعد USE graduation_projects;
-- يحدّث صور المشاريع 1–12 إذا كانت ما زالت default.jpg أو فارغة.

UPDATE projects SET image_url = 'https://picsum.photos/seed/gradproj1/800/450'  WHERE id = 1  AND (image_url = 'default.jpg' OR image_url = '' OR image_url IS NULL);
UPDATE projects SET image_url = 'https://picsum.photos/seed/gradproj2/800/450'  WHERE id = 2  AND (image_url = 'default.jpg' OR image_url = '' OR image_url IS NULL);
UPDATE projects SET image_url = 'https://picsum.photos/seed/gradproj3/800/450'  WHERE id = 3  AND (image_url = 'default.jpg' OR image_url = '' OR image_url IS NULL);
UPDATE projects SET image_url = 'https://picsum.photos/seed/gradproj4/800/450'  WHERE id = 4  AND (image_url = 'default.jpg' OR image_url = '' OR image_url IS NULL);
UPDATE projects SET image_url = 'https://picsum.photos/seed/gradproj5/800/450'  WHERE id = 5  AND (image_url = 'default.jpg' OR image_url = '' OR image_url IS NULL);
UPDATE projects SET image_url = 'https://picsum.photos/seed/gradproj6/800/450'  WHERE id = 6  AND (image_url = 'default.jpg' OR image_url = '' OR image_url IS NULL);
UPDATE projects SET image_url = 'https://picsum.photos/seed/gradproj7/800/450'  WHERE id = 7  AND (image_url = 'default.jpg' OR image_url = '' OR image_url IS NULL);
UPDATE projects SET image_url = 'https://picsum.photos/seed/gradproj8/800/450'  WHERE id = 8  AND (image_url = 'default.jpg' OR image_url = '' OR image_url IS NULL);
UPDATE projects SET image_url = 'https://picsum.photos/seed/gradproj9/800/450'  WHERE id = 9  AND (image_url = 'default.jpg' OR image_url = '' OR image_url IS NULL);
UPDATE projects SET image_url = 'https://picsum.photos/seed/gradproj10/800/450' WHERE id = 10 AND (image_url = 'default.jpg' OR image_url = '' OR image_url IS NULL);
UPDATE projects SET image_url = 'https://picsum.photos/seed/gradproj11/800/450' WHERE id = 11 AND (image_url = 'default.jpg' OR image_url = '' OR image_url IS NULL);
UPDATE projects SET image_url = 'https://picsum.photos/seed/gradproj12/800/450' WHERE id = 12 AND (image_url = 'default.jpg' OR image_url = '' OR image_url IS NULL);
