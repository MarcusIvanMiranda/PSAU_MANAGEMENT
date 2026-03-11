<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once 'connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid user ID']);
    exit();
}

$user_id = (int)$_GET['id'];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$stmt = $conn->prepare("SELECT id, username, full_name, email, department, members, role FROM property_users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    header('Content-Type: application/json');
    echo json_encode($user);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not found']);
}

$stmt->close();
$conn->close();
?>
