-- Migration to make tax_category_id nullable in ospos_items
ALTER TABLE ospos_items
    MODIFY COLUMN tax_category_id INT NULL;
