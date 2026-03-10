-- Create nap_records table for NAP (Records Inventory and Appraisal) Form
-- Pampanga State Agricultural University

CREATE TABLE IF NOT EXISTS `nap_records` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  
  -- 1. NAME OF OFFICE
  `name_of_office` VARCHAR(255) NOT NULL,
  
  -- 2. DEPARTMENT/DIVISION
  `department_division` VARCHAR(255) DEFAULT NULL,
  
  -- 3. SECTION/UNIT
  `section_unit` VARCHAR(255) DEFAULT NULL,
  
  -- 4. TELEPHONE NO.
  `telephone_no` VARCHAR(50) DEFAULT NULL,
  
  -- 5. EMAIL ADDRESS
  `email_address` VARCHAR(255) DEFAULT NULL,
  
  -- 6. ADDRESS
  `address` TEXT DEFAULT NULL,
  
  -- 7. PERSON IN CHARGE
  `person_incharge` VARCHAR(255) DEFAULT NULL,
  
  -- 8. DATE PREPARED
  `date_prepared` DATE DEFAULT NULL,
  
  -- 9. RECORDS SERIES TITLE AND DESCRIPTION
  `records_series_title` TEXT DEFAULT NULL,
  `records_description` TEXT DEFAULT NULL,
  
  -- 10. PERIOD COVERED/INCLUSIVE DATES
  `period_covered_from` VARCHAR(50) DEFAULT NULL,
  `period_covered_to` VARCHAR(50) DEFAULT NULL,
  
  -- 11. VOLUME
  `volume` VARCHAR(50) DEFAULT NULL,
  
  -- 12. RECORDS MEDIUM
  `records_medium` VARCHAR(100) DEFAULT NULL,
  
  -- 13. RESTRICTIONS
  `restrictions` VARCHAR(255) DEFAULT NULL,
  
  -- 14. LOCATION OF RECORDS
  `location_of_records` VARCHAR(255) DEFAULT NULL,
  
  -- 15. REQUEST FREQUENCY
  `request_frequency` VARCHAR(50) DEFAULT NULL,
  
  -- 16. DUPLICATION VALUE (T/P)
  `duplication_value` VARCHAR(10) DEFAULT NULL,
  
  -- 17. TIME VALUE (T/A/L)
  `time_value` VARCHAR(10) DEFAULT NULL,
  
  -- 18. UTILITY VALUE (Adm/F/P/L/Arch)
  `utility_value` VARCHAR(10) DEFAULT NULL,
  
  -- 19. RETENTION PERIOD - ACTIVE, STORAGE and TOTAL
  `retention_period_active` VARCHAR(50) DEFAULT NULL,
  `retention_period_storage` VARCHAR(50) DEFAULT NULL,
  `retention_period_total` VARCHAR(50) DEFAULT NULL,
  
  -- 20. DISPOSITION PROVISION
  `disposition_provision` VARCHAR(255) DEFAULT NULL,
  
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
