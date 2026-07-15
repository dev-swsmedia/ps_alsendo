ALTER TABLE `ps_alsendo_order_shipment`
ADD COLUMN `previous_order_state` INT(11) DEFAULT NULL AFTER `tracking_url`;
