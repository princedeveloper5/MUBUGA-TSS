USE mubuga_tss;

UPDATE users
SET password_hash = '$2y$10$KQqrevjABKnBOSh1IGnl7Or7Jw9an.EABOnbjT8RZiz2PGTBRssCm'
WHERE email = 'admin@mubugatss.rw';
