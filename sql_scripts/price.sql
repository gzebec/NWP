CREATE OR REPLACE TABLE price
(
	id int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY
    , card_id int UNSIGNED NOT NULL
    , price_type_id int UNSIGNED NOT NULL
	, currency varchar(30) NOT NULL
	, vat tinyint UNSIGNED NOT NULL
	, selling_price double(10, 2) NULL
	, selling_price_without_vat double(10, 2) NULL
    , is_deleted boolean NOT NULL DEFAULT(false)
);

ALTER TABLE price
ADD CONSTRAINT fk_price_card FOREIGN KEY(card_id) REFERENCES card (id) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE price
ADD CONSTRAINT fk_price_price_type FOREIGN KEY(price_type_id) REFERENCES price_type (id) ON DELETE CASCADE ON UPDATE RESTRICT;