ALTER TABLE `ospos_attribute_links`
DROP FOREIGN KEY `ospos_attribute_links_ibfk_4`;

ALTER TABLE `ospos_attribute_links`
ADD CONSTRAINT `ospos_attribute_links_ibfk_4`
FOREIGN KEY (`receiving_id`) REFERENCES `ospos_receivings`(`receiving_id`)
ON DELETE CASCADE
ON UPDATE RESTRICT;