-- insert data for new chart

INSERT INTO ospos_permissions(permission_id, module_id, location_id) VALUE ('reports_profits', 'reports', NULL);

INSERT INTO ospos_grants(permission_id, person_id) VALUE ('reports_profits', 1);