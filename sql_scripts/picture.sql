CREATE OR REPLACE TABLE picture
(
	id int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY
	, picture_id int UNSIGNED NOT NULL
    , card_id int UNSIGNED NOT NULL
	, picture_description varchar(255) NOT NULL
	, picture_file varchar(100) NOT NULL
	, picture_default tinyint UNSIGNED NOT NULL
    , is_deleted boolean NOT NULL DEFAULT(false)
);

ALTER TABLE picture
ADD CONSTRAINT fk_picture_card FOREIGN KEY(card_id) REFERENCES card (id) ON DELETE CASCADE ON UPDATE RESTRICT;