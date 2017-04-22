-- alter giftcard field number to be varchar

ALTER TABLE `ospos_giftcards` CHANGE `giftcard_number` `giftcard_number` VARCHAR(255) NULL;