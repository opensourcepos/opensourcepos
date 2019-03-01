ALTER TABLE ospos_attribute_values
ADD COLUMN attribute_decimal DECIMAL(7,3) DEFAULT NULL AFTER attribute_datetime;
