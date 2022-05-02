CREATE OR REPLACE TABLE card
(
	id int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY
    , card_id int UNSIGNED NOT NULL
    , code varchar(10) NOT NULL
	, ean varchar(30) NULL
	, name varchar(100) NOT NULL
	, name_hr varchar(100) NOT NULL
	, text varchar(255) NULL
	, storage varchar(100) NOT NULL
	, storage_hr varchar(100) NOT NULL
	, description varchar(1000) NULL
    , manufacturer varchar(100) NULL
    , count double(10, 2) NOT NULL
    , unit varchar(20) NOT NULL
    , mass double(10, 2) NULL
    , warranty smallint NOT NULL
    , action tinyint UNSIGNED NULL
    , is_deleted boolean NOT NULL DEFAULT(false)
);