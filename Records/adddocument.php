<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Document - PSAU Records System</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="assets/css/psau-style.css">
    <style>
        body {
            margin: 0;
            padding: 1rem;
            background: var(--psau-gray-50);
            font-family: var(--font-sans);
        }
        
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: var(--psau-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid var(--psau-gray-200);
            overflow: hidden;
        }
        
        .form-header {
            background: linear-gradient(135deg, var(--psau-primary) 0%, var(--psau-secondary) 100%);
            color: var(--psau-white);
            padding: 1.5rem;
            text-align: center;
        }
        
        .form-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--psau-white);
        }
        
        .form-subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
            margin: 0;
        }
        
        .form-body {
            padding: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--psau-gray-700);
            font-size: 0.875rem;
        }
        
        .form-control {
            display: block;
            width: 100%;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            line-height: 1.5;
            color: var(--psau-gray-900);
            background-color: var(--psau-white);
            border: 1px solid var(--psau-gray-300);
            border-radius: var(--radius);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            outline: 0;
            border-color: var(--psau-primary);
            box-shadow: 0 0 0 3px rgb(30 90 61 / 0.1);
        }
        
        .form-control::placeholder {
            color: var(--psau-gray-400);
        }
        
        .btn-submit {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--psau-primary);
            color: var(--psau-white);
            border: none;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-submit:hover {
            background: var(--psau-secondary);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .btn-submit:disabled {
            background: var(--psau-gray-300);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .form-footer {
            padding: 1rem 1.5rem;
            background: var(--psau-gray-50);
            border-top: 1px solid var(--psau-gray-200);
            text-align: center;
        }
        
        .form-footer p {
            margin: 0;
            font-size: 0.75rem;
            color: var(--psau-gray-500);
        }
        
        .required-field {
            color: var(--psau-error);
        }
        
        @media (max-width: 640px) {
            body {
                padding: 0.5rem;
            }
            
            .form-container {
                margin: 0;
                border-radius: 0;
            }
            
            .form-body {
                padding: 1rem;
            }
        }
        
        /* Scrollbar Styles */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--psau-accent);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #3d7d54;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h1 class="form-title">Register New Document</h1>
            <p class="form-subtitle">PSAU Records Unit - Document Tracking System</p>
        </div>
        
        <form id="documentForm" action="savedocument.php" method="post">
            <div class="form-body">
                <div class="form-group">
                    <label for="doc_title" class="form-label">
                        Document Title <span class="required-field">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="doc_title" 
                        name="doc_title" 
                        class="form-control" 
                        placeholder="Enter document title" 
                        required
                        onkeypress="return /[0-9a-zA-Z ]/i.test(event.key)"
                    >
                </div>
                
                <div class="form-group">
                    <label for="doc_type" class="form-label">
                        Document Type <span class="required-field">*</span>
                    </label>
                    <select id="doc_type" name="doc_type" class="form-control" required>
                        <option value="INTERNAL">INTERNAL</option>
                        <option value="EXTERNAL">EXTERNAL</option>
                        <option value="OFFICE ORDER">OFFICE ORDER</option>
                        <option value="MEMORANDUM">MEMORANDUM</option>
                        <option value="ANNOUNCEMENT">ANNOUNCEMENT</option>
                        <option value="SPECIAL ORDER">SPECIAL ORDER</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="employeedeatils" class="form-label">
                        Received From <span class="required-field">*</span>
                    </label>
                    <select id="employeedeatils" name="employeedeatils" class="form-control" required>
                        <?php
                        include 'connect.php';
                        $conn = new mysqli($servername, $username, $password, $dbname);
                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }
                        
                        $query = "SELECT * FROM employees ORDER BY fname";
                        $result = mysqli_query($conn, $query);
                        
                        if ($result && mysqli_num_rows($result) > 0) {
                            while($data = mysqli_fetch_row($result)) {
                                $option_value = $data[6] . "-" . $data[3] . " " . $data[2];
                                $option_text = strtoupper($data[3]) . " " . strtoupper($data[2]);
                                echo "<option value='" . $option_value . "'>" . $option_text . "</option>";
                            }
                        }
                        mysqli_close($conn);
                        ?>
                    </select>
                </div>
                
                <input type="hidden" id="RandomSerial" name="RandomSerial">
            </div>
            
            <div class="form-footer">
                <button type="submit" class="btn-submit" id="submitBtn">
                    Register Document
                </button>
                <p style="margin-top: 1rem;">
                    All fields marked with <span class="required-field">*</span> are required
                </p>
            </div>
        </form>
    </div>
    
    <script src="https://randojs.com/1.0.0.js"></script>
    <script>
        // Generate random serial number
        function generateRandomSerial() {
            let serial = '';
            for (let i = 0; i < 8; i++) {
                const randomChar = rando("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789");
                serial = randomChar + serial;
            }
            return serial;
        }
        
        // Form validation and submission
        document.getElementById('documentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const docTitle = document.getElementById('doc_title').value.trim();
            const docType = document.getElementById('doc_type').value;
            const employeeDetails = document.getElementById('employeedeatils').value;
            
            // Validate form
            if (!docTitle) {
                alert('Please enter a document title');
                return;
            }
            
            if (!docType) {
                alert('Please select a document type');
                return;
            }
            
            if (!employeeDetails) {
                alert('Please select who received the document from');
                return;
            }
            
            // Generate and set serial number
            const serialNumber = generateRandomSerial();
            document.getElementById('RandomSerial').value = serialNumber;
            
            // Disable submit button and show loading
            submitBtn.disabled = true;
            submitBtn.textContent = 'Registering...';
            
            // Submit form
            this.submit();
        });
        
        // Generate serial number on page load
        document.addEventListener('DOMContentLoaded', function() {
            const serialNumber = generateRandomSerial();
            document.getElementById('RandomSerial').value = serialNumber;
        });
        
        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
