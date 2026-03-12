<?php
session_start();

require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_username = $_POST['username'];
    $form_password = $_POST['password'];
    
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            header("location: login.php?error=1");
            exit();
        }
        
        // Create property_users table if it doesn't exist
        $conn->query("CREATE TABLE IF NOT EXISTS property_users (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            department VARCHAR(255) DEFAULT NULL,
            members VARCHAR(100) DEFAULT NULL,
            role VARCHAR(50) NOT NULL DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Check if admin user exists, if not create it
        $check_admin = "SELECT id FROM property_users WHERE username = 'admin'";
        $admin_result = $conn->query($check_admin);
        
        if ($admin_result->num_rows === 0) {
            // Create admin user with password admin123
            $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
            $create_admin = "INSERT INTO property_users (username, password, full_name, email, department, members, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($create_admin);
            $stmt->bind_param("sssssss", $admin_username, $admin_password, $admin_fullname, $admin_email, $admin_office, $admin_members, $admin_role);
            
            $admin_username = 'admin';
            $admin_fullname = 'Property Administrator';
            $admin_email = 'property.admin@psau.edu.ph';
            $admin_office = 'PROPERTY MANAGEMENT OFFICE';
            $admin_members = 'Head';
            $admin_role = 'admin';
            
            $stmt->execute();
            $stmt->close();
        }
        
        // Authenticate user
        $sql = "SELECT id, username, password, full_name, department, members, role FROM property_users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $form_username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($form_password, $user['password'])) {
                $_SESSION['property_loggedin'] = true;
                $_SESSION['property_username'] = $user['username'];
                $_SESSION['property_user_id'] = $user['id'];
                $_SESSION['property_full_name'] = $user['full_name'];
                $_SESSION['property_office'] = $user['department'];
                $_SESSION['property_members'] = $user['members'];
                $_SESSION['property_role'] = $user['role'];
                $_SESSION['property_dbname'] = $dbname;
                
                $conn->close();
                
                header("location: index.php");
                exit();
            }
        }
        
        $conn->close();
        header("location: login.php?error=1");
        exit();
        
    } catch (Exception $e) {
        header("location: login.php?error=1");
        exit();
    }
} else {
    header("location: login.php");
    exit();
}
?>
