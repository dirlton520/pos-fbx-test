
ALTER TABLE  `phppos_receivings` ADD  `deleted` INT( 1 ) NOT NULL DEFAULT  '0', ADD INDEX (  `deleted` );