INSERT INTO ospos_stock_locations (location_name) (SELECT DISTINCT(location) FROM ospos_items WHERE NOT EXISTS (select location from ospos_stock_locations where location_name = location));
INSERT INTO ospos_item_quantities (item_id, location_id, quantity) (SELECT item_id, location_id, quantity FROM ospos_items, ospos_stock_locations where ospos_items.location = ospos_stock_locations.location_name);
ALTER TABLE ospos_items DROP COLUMN location;
