<?php
session_start();

require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $conn = new mysqli($servername, "root", "", $dbname);
        
        if ($conn->connect_error) {
            header("location: login.php?error=1");
            exit();
        }
        
        // Create users table if it doesn't exist
        $conn->query("CREATE TABLE IF NOT EXISTS users (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            role VARCHAR(50) NOT NULL DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Check if admin user exists, if not create it
        $check_admin = "SELECT id FROM users WHERE username = 'admin'";
        $admin_result = $conn->query($check_admin);
        
        if ($admin_result->num_rows === 0) {
            // Create admin user with password admin123
            $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
            $create_admin = "INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($create_admin);
            $stmt->bind_param("sssss", $admin_username, $admin_password, $admin_fullname, $admin_email, $admin_role);
            
            $admin_username = 'admin';
            $admin_fullname = 'Administrator';
            $admin_email = 'admin@psau.edu.ph';
            $admin_role = 'admin';
            
            $stmt->execute();
            $stmt->close();
        }
        
        // Authenticate user
        $sql = "SELECT id, username, password, full_name, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['dbname'] = $dbname;
                
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
