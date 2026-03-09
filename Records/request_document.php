<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit();
}

require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['document_id'])) {
    $document_id = $_POST['document_id'];
    $owner_department = $_POST['owner_department'];
    $requester_id = $_SESSION['user_id'];
    
    // Get requester info
    $conn = new mysqli($servername, $username, $password, $dbname);
    $result = $conn->query("SELECT department FROM users WHERE id = " . $requester_id);
    $requester = $result->fetch_assoc();
    $requester_department = $requester['department'];
    
    // Check if request already exists
    $check_sql = "SELECT id FROM document_requests WHERE document_id = ? AND requester_id = ? AND status = 'pending'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $document_id, $requester_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "You have already requested this document.";
    } else {
        // Create new request
        $sql = "INSERT INTO document_requests (document_id, requester_id, requester_department, owner_department) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $document_id, $requester_id, $requester_department, $owner_department);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Document request sent successfully! The office head will review your request.";
        } else {
            $_SESSION['error_message'] = "Error sending request: " . $stmt->error;
        }
    }
    
    $conn->close();
}

header("location: documents.php");
exit();
?>
