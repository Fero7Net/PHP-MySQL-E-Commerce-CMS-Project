-- Admin profil sayfası için full_name kolonu (users tablosu)
-- Hata alırsanız kolon zaten mevcut demektir
ALTER TABLE users ADD COLUMN full_name VARCHAR(150) NULL AFTER email;
