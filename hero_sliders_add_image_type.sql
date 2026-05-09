ALTER TABLE hero_sliders 
ADD COLUMN heading VARCHAR(255) NULL AFTER image_type,
ADD COLUMN subheading TEXT NULL AFTER heading,
ADD COLUMN button_text VARCHAR(255) NULL AFTER subheading,
ADD COLUMN button_url VARCHAR(255) NULL AFTER button_text;
