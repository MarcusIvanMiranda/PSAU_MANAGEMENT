<?php
require_once 'connect.php';

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Add office column to property_users table if it doesn't exist
    $alter_sql = "ALTER TABLE property_users ADD COLUMN office VARCHAR(100) DEFAULT NULL AFTER email";
    
    if ($conn->query($alter_sql)) {
        echo "Office column added successfully to property_users table.<br>";
    } else {
        // Check if column already exists
        if ($conn->errno == 1060) {
            echo "Office column already exists in property_users table.<br>";
        } else {
            echo "Error adding office column: " . $conn->error . "<br>";
        }
    }
    
    // Add members column to property_users table if it doesn't exist
    $alter_members_sql = "ALTER TABLE property_users ADD COLUMN members VARCHAR(100) DEFAULT NULL AFTER office";
    
    if ($conn->query($alter_members_sql)) {
        echo "Members column added successfully to property_users table.<br>";
    } else {
        // Check if column already exists
        if ($conn->errno == 1060) {
            echo "Members column already exists in property_users table.<br>";
        } else {
            echo "Error adding members column: " . $conn->error . "<br>";
        }
    }
    
    // Update existing admin user to have an office and members
    $update_admin = "UPDATE property_users SET office = 'PROPERTY MANAGEMENT OFFICE', members = 'Head' WHERE username = 'admin'";
    if ($conn->query($update_admin)) {
        echo "Admin user updated with office and members information.<br>";
    }
    
    $conn->close();
    echo "<br><a href='manage_accounts.php'>Go to Account Management</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
