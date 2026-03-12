-- ============================================
-- Property Transfer History Table
-- For tracking item transfers between accountable persons
-- ============================================

CREATE TABLE IF NOT EXISTS `property_transfers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `property_tag` varchar(45) DEFAULT NULL,
  `property_no` varchar(45) DEFAULT NULL,
  `previous_owner` varchar(200) DEFAULT NULL,
  `new_owner` varchar(200) NOT NULL,
  `previous_location` varchar(200) DEFAULT NULL,
  `new_location` varchar(200) DEFAULT NULL,
  `transfer_reason` text DEFAULT NULL,
  `transfer_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `transferred_by` varchar(100) DEFAULT NULL,
  `transfer_type` varchar(50) DEFAULT 'Transfer',
  `approved_by` varchar(200) DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `property_tag` (`property_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
