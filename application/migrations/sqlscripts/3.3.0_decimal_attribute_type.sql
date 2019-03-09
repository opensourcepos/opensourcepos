ALTER TABLE ospos_attribute_values
ADD COLUMN attribute_decimal DECIMAL(7,3) DEFAULT NULL AFTER attribute_datetime;

ALTER TABLE ospos_attribute_definitions
ADD COLUMN definition_unit VARCHAR(16) DEFAULT NULL AFTER definition_type;