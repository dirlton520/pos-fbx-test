-- ebt_non_integrated_flag --

ALTER TABLE phppos_locations ADD ebt_integrated int(1) NOT NULL DEFAULT 0;
UPDATE phppos_locations SET ebt_integrated = 1;
