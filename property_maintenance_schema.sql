-- Create table for repair/maintenance/replace parts costs with logging
CREATE TABLE IF NOT EXISTS `property_maintenance_costs` (
  `idmaintenance_cost` int NOT NULL AUTO_INCREMENT,
  `property_id` int NOT NULL,
  `property_tag` varchar(45) NOT NULL,
  `cost_type` enum('repair','maintenance','replace') NOT NULL,
  `cost_description` varchar(500) DEFAULT NULL,
  `cost_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cost_date` date NOT NULL,
  `performed_by` varchar(200) DEFAULT NULL,
  `supplier_vendor` varchar(200) DEFAULT NULL,
  `invoice_reference` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` varchar(200) DEFAULT NULL,
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idmaintenance_cost`),
  KEY `idx_property_id` (`property_id`),
  KEY `idx_property_tag` (`property_tag`),
  KEY `idx_cost_date` (`cost_date`),
  CONSTRAINT `fk_maintenance_property` FOREIGN KEY (`property_id`) REFERENCES `property_list` (`idproperty_list`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Add addition_cost column to property_list table if it doesn't exist
ALTER TABLE `property_list` 
ADD COLUMN IF NOT EXISTS `addition_cost` decimal(12,2) NOT NULL DEFAULT 0.00 
AFTER `property_value`;

-- Create view for property with total maintenance costs
CREATE OR REPLACE VIEW `property_with_costs` AS
SELECT 
    p.*,
    COALESCE(SUM(m.cost_amount), 0) as total_maintenance_cost,
    (COALESCE(p.property_value, 0) + COALESCE(p.addition_cost, 0) + COALESCE(SUM(m.cost_amount), 0)) as total_cost_with_maintenance
FROM property_list p
LEFT JOIN property_maintenance_costs m ON p.idproperty_list = m.property_id
GROUP BY p.idproperty_list;
