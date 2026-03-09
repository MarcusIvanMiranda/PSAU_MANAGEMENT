-- Update property_users table to include office and members fields
-- Run this script to add the missing columns

-- Add office column after email
ALTER TABLE property_users ADD COLUMN office VARCHAR(100) DEFAULT NULL AFTER email;

-- Add members column after office  
ALTER TABLE property_users ADD COLUMN members VARCHAR(100) DEFAULT NULL AFTER office;

-- Update existing admin user with office and members information
UPDATE property_users SET office = 'PROPERTY MANAGEMENT OFFICE', members = 'Head' WHERE username = 'admin';

-- Show updated table structure
DESCRIBE property_users;
