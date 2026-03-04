<!DOCTYPE html>

<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit();
}

include "connect.php";
$doc_title=strtoupper($_POST['doc_title']);
$doc_type=strtoupper($_POST['doc_type']);
$doc_serial=strtoupper($_POST['RandomSerial']);
$doc_receipt=strtoupper($_POST['employeedeatils']);
$doc_office=substr($doc_receipt,0,strrpos($doc_receipt,"-")-1);
$doc_employee=substr($doc_receipt,strrpos($doc_receipt,"-")+1,250);
$doc_datecode = "".date("Ymd");
$dateandserial=strval($doc_datecode).$doc_serial;
$added_by = $_SESSION['user_id'];


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: Registration unsuccessful" . $conn->connect_error);
}
$sql = "INSERT INTO records_document_main (document_title, document_type, serial_code, received_from, employee_receipt, document_status, added_by)
 VALUES ('$doc_title', '$doc_type', '$doc_datecode-$doc_serial', '$doc_office', '$doc_employee', 'FOR RELEASING', $added_by)";
if ($conn->query($sql) === TRUE) {
  $success_message = "Document '$doc_title' has been successfully registered with serial code: $doc_datecode-$doc_serial";
} else {
  $success_message = "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Registration - PSAU Records System</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="assets/css/psau-style.css">
    <style>
        body {
            margin: 0;
            padding: 1rem;
            background: var(--psau-gray-50);
            font-family: var(--font-sans);
        }
        
        .message-container {
            max-width: 600px;
            margin: 2rem auto;
            background: var(--psau-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid var(--psau-gray-200);
            overflow: hidden;
        }
        
        .message-header {
            background: linear-gradient(135deg, var(--psau-primary) 0%, var(--psau-secondary) 100%);
            color: var(--psau-white);
            padding: 1.5rem;
            text-align: center;
        }
        
        .message-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .message-body {
            padding: 2rem;
            text-align: center;
        }
        
        .success-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .message-text {
            font-size: 1rem;
            color: var(--psau-gray-700);
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        
        .btn-container {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: var(--psau-primary);
            color: var(--psau-white);
        }
        
        .btn-primary:hover {
            background: var(--psau-secondary);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-secondary {
            background: var(--psau-gray-200);
            color: var(--psau-gray-700);
        }
        
        .btn-secondary:hover {
            background: var(--psau-gray-300);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        @media (max-width: 640px) {
            body {
                padding: 0.5rem;
            }
            
            .message-container {
                margin: 1rem 0;
                border-radius: 0;
            }
            
            .btn-container {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="message-container">
        <div class="message-header">
            <h1 class="message-title">Document Registration Status</h1>
        </div>
        
        <div class="message-body">
            <div class="success-icon">✅</div>
            <div class="message-text">
                <?php echo $success_message; ?>
            </div>
            
            <div class="btn-container">
                <button class="btn btn-primary" onclick="registerAnother()">
                    <span>📝</span>
                    Register Another Document
                </button>
                <button class="btn btn-secondary" onclick="goHome()">
                    <span>🏠</span>
                    Go to Home
                </button>
            </div>
        </div>
    </div>
    
    <script>
        function registerAnother() {
            window.location.href = 'adddocument.php';
        }
        
        function goHome() {
            if (window.parent && window.parent.loadPage) {
                // If loaded in iframe, use parent's navigation
                window.parent.loadPage('logbook.php');
            } else {
                // If accessed directly, redirect to main page
                window.location.href = 'index.php';
            }
        }
        
        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>