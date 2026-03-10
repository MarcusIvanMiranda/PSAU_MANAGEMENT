<?php
require_once '../connect.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success_message = '';
$error_message = '';
$validation_errors = [];

// Fetch offices and sub-offices for dropdowns
$offices_result = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name");
$offices = [];
if ($offices_result) {
    while ($row = $offices_result->fetch_assoc()) {
        $offices[] = $row;
    }
}

// Fetch sub-offices grouped by office_id
$sub_offices_result = $conn->query("SELECT office_id, sub_name FROM sub_offices ORDER BY office_id, sub_name");
$sub_offices = [];
if ($sub_offices_result) {
    while ($row = $sub_offices_result->fetch_assoc()) {
        $sub_offices[$row['office_id']][] = $row['sub_name'];
    }
}

// Get current logged-in user's full name for Person-in-Charge
session_start();
$current_user_full_name = '';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_result = $conn->query("SELECT full_name FROM users WHERE id = $user_id");
    if ($user_result) {
        $user_data = $user_result->fetch_assoc();
        $current_user_full_name = $user_data['full_name'] ?? '';
    }
}

// Get current date for 'Date Prepared' field
$current_date = date('Y-m-d');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_record'])) {
    // Get all 20 fields
    $name_of_office        = trim($_POST['name_of_office'] ?? '');
    $department_division   = trim($_POST['department_division'] ?? '');
    $section_unit          = trim($_POST['section_unit'] ?? '');
    $telephone_no          = trim($_POST['telephone_no'] ?? '');
    $email_address         = trim($_POST['email_address'] ?? '');
    $address               = trim($_POST['address'] ?? '');
    $person_incharge       = trim($_POST['person_incharge'] ?? '');
    $date_prepared         = trim($_POST['date_prepared'] ?? '');
    $records_series_title  = trim($_POST['records_series_title'] ?? '');
    $records_description   = trim($_POST['records_description'] ?? '');
    $period_covered_from   = trim($_POST['period_covered_from'] ?? '');
    $volume                = trim($_POST['volume'] ?? '');
    $records_medium        = trim($_POST['records_medium'] ?? '');
    $restrictions          = trim($_POST['restrictions'] ?? '');
    $location_of_records   = trim($_POST['location_of_records'] ?? '');
    $request_frequency     = trim($_POST['request_frequency'] ?? '');
    $duplication_value     = trim($_POST['duplication_value'] ?? '');
    $time_value            = trim($_POST['time_value'] ?? '');
    $utility_value         = trim($_POST['utility_value'] ?? '');
    $retention_period_active  = trim($_POST['retention_period_active'] ?? '');
    $retention_period_storage = trim($_POST['retention_period_storage'] ?? '');
    $retention_period_total   = trim($_POST['retention_period_total'] ?? '');
    $disposition_provision    = trim($_POST['disposition_provision'] ?? '');

    // Validation - check required fields
    $required_fields = [
        'name_of_office' => 'Name of Office',
        'department_division' => 'Department/Division',
        'section_unit' => 'Section/Unit',
        'address' => 'Address',
        'person_incharge' => 'Person-in-Charge',
        'date_prepared' => 'Date Prepared',
        'records_series_title' => 'Records Series Title',
        'period_covered_from' => 'Period Covered',
        'volume' => 'Volume',
        'records_medium' => 'Records Medium',
        'retention_period_active' => 'Retention Active',
        'disposition_provision' => 'Disposition'
    ];

    // Only require retention storage and total if not permanent
    if ($time_value !== 'P') {
        $required_fields['retention_period_storage'] = 'Retention Storage';
        $required_fields['retention_period_total'] = 'Retention Total';
    }

    foreach ($required_fields as $field => $label) {
        if (empty(${$field})) {
            $validation_errors[] = "$label is required";
        }
    }

    if (empty($validation_errors)) {
        $sql = "INSERT INTO nap_records (
            name_of_office, department_division, section_unit, telephone_no, email_address,
            address, person_incharge, date_prepared, records_series_title, records_description,
            period_covered_from, volume, records_medium, restrictions,
            location_of_records, request_frequency, duplication_value, time_value, utility_value,
            retention_period_active, retention_period_storage, retention_period_total, disposition_provision
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssssssssssssss",
            $name_of_office, $department_division, $section_unit, $telephone_no, $email_address,
            $address, $person_incharge, $date_prepared, $records_series_title, $records_description,
            $period_covered_from, $volume, $records_medium, $restrictions,
            $location_of_records, $request_frequency, $duplication_value, $time_value, $utility_value,
            $retention_period_active, $retention_period_storage, $retention_period_total, $disposition_provision
        );

        if ($stmt->execute()) {
            $success_message = "Record saved successfully!";
        } else {
            $error_message = "Error saving record: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Please fill in all required fields.";
    }
}

// Fetch saved records for display table
$section_filter = $_GET['section_filter'] ?? '';
$sql = "SELECT * FROM nap_records";
if (!empty($section_filter)) {
    $sql .= " WHERE section_unit = '" . $conn->real_escape_string($section_filter) . "'";
}
$sql .= " ORDER BY created_at DESC LIMIT 10";
$saved_records = $conn->query($sql);

// Get unique sections for filter dropdown
$sections_result = $conn->query("SELECT DISTINCT section_unit FROM nap_records WHERE section_unit IS NOT NULL AND section_unit != '' ORDER BY section_unit");
$sections = [];
if ($sections_result) {
    while ($row = $sections_result->fetch_assoc()) {
        $sections[] = $row['section_unit'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NAP Records - Entry Form</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            background: #525659;
            padding: 20px;
        }

        /* ── No-print entry form ── */
        .no-print {
            background: #fff;
            padding: 15px 20px;
            width: 1200px;
            margin: 0 auto 20px auto;
            border-radius: 4px;
        }
        .no-print h3 { margin-bottom: 10px; }
        .no-print input[type="text"] {
            padding: 5px 8px;
            margin-right: 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 13px;
        }
        .no-print button {
            padding: 5px 14px;
            margin-right: 6px;
            cursor: pointer;
            font-size: 13px;
        }

        /* ── Navigation bar ── */
        .nav-bar {
            background: #fff;
            padding: 15px 20px;
            width: 1200px;
            margin: 0 auto 20px auto;
            border-radius: 4px;
            display: flex;
            gap: 10px;
        }
        .nav-bar a {
            padding: 6px 14px;
            text-decoration: none;
            color: white;
            background: #117a65;
            border-radius: 3px;
            font-size: 13px;
        }
        .nav-bar a:hover {
            background: #0e6655;
        }

        /* ── Messages ── */
        .success-msg {
            color: green;
            margin-bottom: 8px;
        }
        .error-msg {
            color: red;
            margin-bottom: 8px;
        }
        .validation-errors {
            color: red;
            margin-bottom: 8px;
            font-size: 12px;
        }
        .validation-errors ul {
            margin: 0;
            padding-left: 20px;
        }

        /* ── Records Table ── */
        .records-table {
            background: #fff;
            padding: 15px 20px;
            width: 1200px;
            margin: 20px auto;
            border-radius: 4px;
        }
        .records-table h3 {
            margin-bottom: 15px;
            color: #333;
        }
        .records-table table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .records-table th,
        .records-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .records-table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .records-table tr:hover {
            background: #f9f9f9;
        }
        .records-table .title-cell {
            max-width: 200px;
        }
        .records-table .retention-cell {
            white-space: nowrap;
        }
        .records-table table {
            font-size: 10px;
        }
        .records-table th,
        .records-table td {
            padding: 4px;
        }

        /* ── Filter Section ── */
        .filter-section {
            background: #fff;
            padding: 15px 20px;
            width: 1200px;
            margin: 20px auto;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .filter-section label {
            font-weight: bold;
            font-size: 13px;
        }
        .filter-section select {
            padding: 5px 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 13px;
            min-width: 150px;
        }
        .filter-section button {
            padding: 5px 14px;
            background: #117a65;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13px;
        }
        .filter-section button:hover {
            background: #0e6655;
        }
    </style>
</head>
<body>

<!-- ══════════════════════════════
     NAVIGATION BAR
     ══════════════════════════════ -->
<div class="nav-bar">
</div>

<!-- ══════════════════════════════
     ENTRY FORM - All 20 Columns
     ══════════════════════════════ -->
<div class="no-print">
    <h3>Add Record Entry</h3>
    <?php if (!empty($success_message)): ?>
        <p style="color:green; margin-bottom:8px;"><?= $success_message ?></p>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <p style="color:red; margin-bottom:8px;"><?= $error_message ?></p>
    <?php endif; ?>
    <?php if (!empty($validation_errors)): ?>
        <div class="validation-errors">
            <ul>
                <?php foreach ($validation_errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" style="display:flex; flex-wrap:wrap; gap:6px; align-items:flex-end;">
        <!-- Column 1-8: Office Information -->
        <div><label style="font-size:11px;display:block;">1. Name of Office *</label>
            <select name="name_of_office" style="width:200px; padding:5px 8px; border:1px solid #ccc; border-radius:3px; font-size:13px;" required>
                <option value="Pampanga State Agricultural University" selected>Pampanga State Agricultural University</option>
            </select></div>
        <div><label style="font-size:11px;display:block;">2. Dept/Division *</label>
            <select name="department_division" id="department_division" style="width:180px; padding:5px 8px; border:1px solid #ccc; border-radius:3px; font-size:13px;" required>
                <option value="">Select Department</option>
                <?php foreach ($offices as $office): ?>
                    <option value="<?= htmlspecialchars($office['office_name']) ?>" data-office-id="<?= $office['id'] ?>" <?= (($_POST['department_division'] ?? '') == $office['office_name']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($office['office_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select></div>
        <div><label style="font-size:11px;display:block;">3. Section/Unit *</label>
            <select name="section_unit" id="section_unit" style="width:140px; padding:5px 8px; border:1px solid #ccc; border-radius:3px; font-size:13px;" required onchange="updateLocation()">
                <option value="">Select Section</option>
                <?php 
                // Show sub-offices for the currently selected department
                $selected_dept = $_POST['department_division'] ?? '';
                if ($selected_dept) {
                    // Find the office_id for the selected department
                    $selected_office_id = null;
                    foreach ($offices as $office) {
                        if ($office['office_name'] === $selected_dept) {
                            $selected_office_id = $office['id'];
                            break;
                        }
                    }
                    // Display sub-offices for this office
                    if ($selected_office_id && isset($sub_offices[$selected_office_id])) {
                        foreach ($sub_offices[$selected_office_id] as $sub_office) {
                            echo '<option value="' . htmlspecialchars($sub_office) . '" ' . 
                                 (($_POST['section_unit'] ?? '') == $sub_office ? 'selected' : '') . '>' . 
                                 htmlspecialchars($sub_office) . '</option>';
                        }
                    }
                }
                ?>
            </select></div>
        <div><label style="font-size:11px;display:block;">4. Telephone No.</label>
            <input type="text" name="telephone_no" style="width:100px;" value="<?= htmlspecialchars($_POST['telephone_no'] ?? '') ?>"></div>
        <div><label style="font-size:11px;display:block;">5. Email Address</label>
            <input type="text" name="email_address" style="width:160px;" value="<?= htmlspecialchars($_POST['email_address'] ?? '') ?>"></div>
        <div><label style="font-size:11px;display:block;">6. Address *</label>
            <select name="address" style="width:180px; padding:5px 8px; border:1px solid #ccc; border-radius:3px; font-size:13px;" required>
                <option value="Magalang Pampanga" selected>Magalang Pampanga</option>
            </select></div>
        <div><label style="font-size:11px;display:block;">7. Person-in-Charge *</label>
            <input type="text" name="person_incharge" style="width:160px; padding:5px 8px; border:1px solid #ccc; border-radius:3px; font-size:13px;" value="<?= htmlspecialchars($current_user_full_name ?: ($_POST['person_incharge'] ?? '')) ?>" readonly></div>
        <div><label style="font-size:11px;display:block;">8. Date Prepared *</label>
            <input type="text" name="date_prepared" placeholder="YYYY-MM-DD" style="width:110px;" value="<?= htmlspecialchars($_POST['date_prepared'] ?? $current_date) ?>" readonly></div>
        
        <div style="width:100%;"></div><!-- Line break -->
        
        <!-- Column 9-14: Record Details -->
        <div><label style="font-size:11px;display:block;">9a. Series Title *</label>
            <input type="text" name="records_series_title" style="width:200px;" value="<?= htmlspecialchars($_POST['records_series_title'] ?? '') ?>"></div>
        <div><label style="font-size:11px;display:block;">9b. Description</label>
            <input type="text" name="records_description" style="width:180px;" value="<?= htmlspecialchars($_POST['records_description'] ?? '') ?>"></div>
        <div><label style="font-size:11px;display:block;">10. Period Covered *</label>
            <input type="text" name="period_covered_from" placeholder="e.g. Sep-24" style="width:90px;" value="<?= htmlspecialchars($_POST['period_covered_from'] ?? '') ?>"></div>
        <div><label style="font-size:11px;display:block;">11. Volume *</label>
            <input type="text" name="volume" style="width:70px;" value="<?= htmlspecialchars($_POST['volume'] ?? '') ?>"></div>
        <div><label style="font-size:11px;display:block;">12. Medium *</label>
            <select name="records_medium" style="width:90px; padding:5px 8px; border:1px solid #ccc; border-radius:3px; font-size:13px;">
                <option value="">Select</option>
                <option value="Paper" <?= (($_POST['records_medium'] ?? '') == 'Paper') ? 'selected' : '' ?>>Paper</option>
                <option value="Micro Film" <?= (($_POST['records_medium'] ?? '') == 'Micro Film') ? 'selected' : '' ?>>Micro Film</option>
                <option value="Electronic" <?= (($_POST['records_medium'] ?? '') == 'Electronic') ? 'selected' : '' ?>>Electronic</option>
                <option value="CD/DVD" <?= (($_POST['records_medium'] ?? '') == 'CD/DVD') ? 'selected' : '' ?>>CD/DVD</option>
                <option value="Maps" <?= (($_POST['records_medium'] ?? '') == 'Maps') ? 'selected' : '' ?>>Maps</option>
                <option value="Drawings" <?= (($_POST['records_medium'] ?? '') == 'Drawings') ? 'selected' : '' ?>>Drawings</option>
                <option value="Computer Print Out" <?= (($_POST['records_medium'] ?? '') == 'Computer Print Out') ? 'selected' : '' ?>>Computer Print Out</option>
            </select></div>
        <div><label style="font-size:11px;display:block;">13. Restrictions</label>
            <select name="restrictions" style="width:90px; padding:5px 8px; border:1px solid #ccc; border-radius:3px; font-size:13px;">
                <option value="">Select</option>
                <option value="Top Secret" <?= (($_POST['restrictions'] ?? '') == 'Top Secret') ? 'selected' : '' ?>>Top Secret</option>
                <option value="Secret" <?= (($_POST['restrictions'] ?? '') == 'Secret') ? 'selected' : '' ?>>Secret</option>
                <option value="Confidential" <?= (($_POST['restrictions'] ?? '') == 'Confidential') ? 'selected' : '' ?>>Confidential</option>
                <option value="Restricted" <?= (($_POST['restrictions'] ?? '') == 'Restricted') ? 'selected' : '' ?>>Restricted</option>
                <option value="Open Access" <?= (($_POST['restrictions'] ?? '') == 'Open Access') ? 'selected' : '' ?>>Open Access</option>
            </select></div>
        
        <div style="width:100%;"></div><!-- Line break -->
        
        <!-- Column 15-19: Usage & Value -->
        <div><label style="font-size:11px;display:block;">14. Location</label>
            <input type="text" name="location_of_records" id="location_of_records" style="width:100px;" value="<?= htmlspecialchars($_POST['location_of_records'] ?? '') ?>"></div>
        <div><label style="font-size:11px;display:block;">15. Frequency</label>
            <select name="request_frequency" style="width:100px; padding:5px 8px; border:1px solid #ccc; border-radius:3px; font-size:13px;">
                <option value="">Select</option>
                <option value="Daily" <?= (($_POST['request_frequency'] ?? '') == 'Daily') ? 'selected' : '' ?>>Daily</option>
                <option value="Weekly" <?= (($_POST['request_frequency'] ?? '') == 'Weekly') ? 'selected' : '' ?>>Weekly</option>
                <option value="Monthly" <?= (($_POST['request_frequency'] ?? '') == 'Monthly') ? 'selected' : '' ?>>Monthly</option>
                <option value="Semi-Annually" <?= (($_POST['request_frequency'] ?? '') == 'Semi-Annually') ? 'selected' : '' ?>>Semi-Annually</option>
                <option value="Annually" <?= (($_POST['request_frequency'] ?? '') == 'Annually') ? 'selected' : '' ?>>Annually</option>
                <option value="Quarterly" <?= (($_POST['request_frequency'] ?? '') == 'Quarterly') ? 'selected' : '' ?>>Quarterly</option>
                <option value="ANA" <?= (($_POST['request_frequency'] ?? '') == 'ANA') ? 'selected' : '' ?>>ANA</option>
            </select></div>
        <div><label style="font-size:11px;display:block;">16. Duplication</label>
            <select name="duplication_value" style="width:70px; padding:5px 8px; border:1px solid #ccc; border-radius:3px; font-size:13px;">
                <option value="N/A" <?= (($_POST['duplication_value'] ?? 'N/A') == 'N/A') ? 'selected' : '' ?>>N/A</option>
                <?php foreach ($offices as $office): ?>
                    <option value="<?= htmlspecialchars($office['office_name']) ?>" <?= (($_POST['duplication_value'] ?? '') == $office['office_name']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($office['office_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select></div>
        <div><label style="font-size:11px;display:block;">17. Time Value (T/P)</label>
            <select name="time_value" id="time_value" style="width:60px; padding:5px 8px; border:1px solid #ccc; border-radius:3px; font-size:13px;" onchange="updateDispositionAndRetention()">
                <option value="">Select</option>
                <option value="T" <?= (($_POST['time_value'] ?? '') == 'T') ? 'selected' : '' ?>>T - Temporary</option>
                <option value="P" <?= (($_POST['time_value'] ?? '') == 'P') ? 'selected' : '' ?>>P - Permanent</option>
            </select></div>
        <div><label style="font-size:11px;display:block;">18. Utility (Adm/F/L/Arc)</label>
            <select name="utility_value" style="width:100px; padding:5px 8px; border:1px solid #ccc; border-radius:3px; font-size:13px;">
                <option value="">Select</option>
                <option value="Adm" <?= (($_POST['utility_value'] ?? '') == 'Adm') ? 'selected' : '' ?>>Adm - Administrative</option>
                <option value="F" <?= (($_POST['utility_value'] ?? '') == 'F') ? 'selected' : '' ?>>F - Fiscal</option>
                <option value="L" <?= (($_POST['utility_value'] ?? '') == 'L') ? 'selected' : '' ?>>L - Legal</option>
                <option value="Arc" <?= (($_POST['utility_value'] ?? '') == 'Arc') ? 'selected' : '' ?>>Arc - Archival</option>
            </select></div>
        
        <div style="width:100%;"></div><!-- Line break -->
        
        <!-- Column 19: Retention Period -->
        <div><label style="font-size:11px;display:block;">19a. Ret. Active *</label>
            <input type="number" name="retention_period_active" id="retention_period_active" style="width:80px;" value="<?= htmlspecialchars($_POST['retention_period_active'] ?? '') ?>" onchange="calculateRetentionTotal()"></div>
        <div><label style="font-size:11px;display:block;">19b. Ret. Storage *</label>
            <input type="number" name="retention_period_storage" id="retention_period_storage" style="width:80px;" value="<?= htmlspecialchars($_POST['retention_period_storage'] ?? '') ?>" onchange="calculateRetentionTotal()"></div>
        <div><label style="font-size:11px;display:block;">19c. Ret. Total *</label>
            <input type="number" name="retention_period_total" id="retention_period_total" style="width:80px;" value="<?= htmlspecialchars($_POST['retention_period_total'] ?? '') ?>" readonly></div>
        <div><label style="font-size:11px;display:block;">20. Disposition *</label>
            <input type="text" name="disposition_provision" id="disposition_provision" style="width:150px;" value="<?= htmlspecialchars($_POST['disposition_provision'] ?? '') ?>"></div>

        <div style="align-self:flex-end;">
            <button type="submit" name="submit_record" style="background:#1a5276;color:white;padding:6px 14px;border:none;border-radius:3px;">Add Row</button>
        </div>
    </form>
</div>

<!-- ══════════════════════════════
     FILTER SECTION
     ══════════════════════════════ -->
<div class="filter-section">
    <label for="section_filter">Filter by Section:</label>
    <form method="GET" style="display: flex; gap: 10px; align-items: center;">
        <select name="section_filter" id="section_filter" onchange="this.form.submit()">
            <option value="">All Sections</option>
            <?php foreach ($sections as $section): ?>
                <option value="<?= htmlspecialchars($section) ?>" <?= ($section_filter == $section) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($section) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($section_filter)): ?>
            <button type="button" onclick="window.location.href='index.php'">Clear Filter</button>
        <?php endif; ?>
        <a href="records_table.php?section_filter=<?= urlencode($section_filter) ?>" style="background:#117a65;color:white;padding:5px 14px;border-radius:3px;text-decoration:none;font-size:13px;">📋 View Records Table</a>
    </form>
    <?php if (!empty($section_filter)): ?>
        <span style="color: #666; font-size: 12px;">Showing records for: <strong><?= htmlspecialchars($section_filter) ?></strong></span>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════
     SAVED RECORDS TABLE
     ══════════════════════════════ -->
<div class="records-table">
    <h3>Recently Saved Records (Last 10)</h3>
    <?php if ($saved_records && $saved_records->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th style="width: 3%;">ID</th>
                <th style="width: 12%;">Records Series Title</th>
                <th style="width: 8%;">Description</th>
                <th style="width: 8%;">Name of Office</th>
                <th style="width: 8%;">Department</th>
                <th style="width: 6%;">Section</th>
                <th style="width: 5%;">Telephone</th>
                <th style="width: 8%;">Email</th>
                <th style="width: 6%;">Address</th>
                <th style="width: 8%;">Person-in-Charge</th>
                <th style="width: 5%;">Date Prepared</th>
                <th style="width: 5%;">Period Covered</th>
                <th style="width: 4%;">Volume</th>
                <th style="width: 6%;">Medium</th>
                <th style="width: 6%;">Restrictions</th>
                <th style="width: 6%;">Location</th>
                <th style="width: 5%;">Frequency</th>
                <th style="width: 6%;">Duplication</th>
                <th style="width: 4%;">Time Value</th>
                <th style="width: 5%;">Utility Value</th>
                <th style="width: 8%;">Retention Period</th>
                <th style="width: 6%;">Disposition</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $saved_records->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td class="title-cell">
                    <strong><?= htmlspecialchars($row['records_series_title']) ?></strong>
                </td>
                <td>
                    <small><?= htmlspecialchars(substr($row['records_description'], 0, 50)) ?><?= strlen($row['records_description']) > 50 ? '...' : '' ?></small>
                </td>
                <td><?= htmlspecialchars($row['name_of_office']) ?></td>
                <td><?= htmlspecialchars($row['department_division']) ?></td>
                <td><?= htmlspecialchars($row['section_unit']) ?></td>
                <td><?= htmlspecialchars($row['telephone_no']) ?></td>
                <td><?= htmlspecialchars($row['email_address']) ?></td>
                <td><?= htmlspecialchars($row['address']) ?></td>
                <td><?= htmlspecialchars($row['person_incharge']) ?></td>
                <td><?= htmlspecialchars($row['date_prepared']) ?></td>
                <td><?= htmlspecialchars($row['period_covered_from']) ?></td>
                <td><?= htmlspecialchars($row['volume']) ?></td>
                <td><?= htmlspecialchars($row['records_medium']) ?></td>
                <td><?= htmlspecialchars($row['restrictions']) ?></td>
                <td><?= htmlspecialchars($row['location_of_records']) ?></td>
                <td><?= htmlspecialchars($row['request_frequency']) ?></td>
                <td><?= htmlspecialchars($row['duplication_value']) ?></td>
                <td><?= htmlspecialchars($row['time_value']) ?></td>
                <td><?= htmlspecialchars($row['utility_value']) ?></td>
                <td class="retention-cell">
                    <small>A: <?= htmlspecialchars($row['retention_period_active']) ?></small><br>
                    <small>S: <?= htmlspecialchars($row['retention_period_storage']) ?></small><br>
                    <small>T: <?= htmlspecialchars($row['retention_period_total']) ?></small>
                </td>
                <td><?= htmlspecialchars($row['disposition_provision']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p style="color: #666; text-align: center; padding: 20px;">No records saved yet. Use the form above to add records.</p>
    <?php endif; ?>
</div>

<script>
// Sub-offices data from PHP
const subOfficesData = <?= json_encode($sub_offices) ?>;

// Function to update section/unit dropdown based on department selection
function updateSectionUnit() {
    const departmentSelect = document.getElementById('department_division');
    const sectionSelect = document.getElementById('section_unit');
    const selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
    const officeId = selectedOption.getAttribute('data-office-id');
    
    // Clear current options
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    
    if (officeId && subOfficesData[officeId]) {
        subOfficesData[officeId].forEach(function(subOffice) {
            const option = document.createElement('option');
            option.value = subOffice;
            option.textContent = subOffice;
            sectionSelect.appendChild(option);
        });
    }
}

// Add event listener to department dropdown
document.addEventListener('DOMContentLoaded', function() {
    const departmentSelect = document.getElementById('department_division');
    if (departmentSelect) {
        departmentSelect.addEventListener('change', updateSectionUnit);
    }
    
    // Calculate initial retention total
    calculateRetentionTotal();
});

// Function to calculate retention total
function calculateRetentionTotal() {
    const activeInput = document.getElementById('retention_period_active');
    const storageInput = document.getElementById('retention_period_storage');
    const totalInput = document.getElementById('retention_period_total');
    
    if (activeInput && storageInput && totalInput) {
        const active = parseFloat(activeInput.value) || 0;
        const storage = parseFloat(storageInput.value) || 0;
        const total = active + storage;
        totalInput.value = total;
    }
}

// Function to update location field when section/unit changes
function updateLocation() {
    const sectionSelect = document.getElementById('section_unit');
    const locationInput = document.getElementById('location_of_records');
    
    if (sectionSelect && locationInput) {
        locationInput.value = sectionSelect.value;
    }
}

// Function to update disposition and clear retention fields when time value changes
function updateDispositionAndRetention() {
    const timeValueSelect = document.getElementById('time_value');
    const dispositionInput = document.getElementById('disposition_provision');
    const storageInput = document.getElementById('retention_period_storage');
    const totalInput = document.getElementById('retention_period_total');
    
    if (timeValueSelect && dispositionInput) {
        if (timeValueSelect.value === 'P') { // Permanent selected
            dispositionInput.value = 'Permanent';
            // Set storage and total fields to "---"
            if (storageInput) storageInput.value = '---';
            if (totalInput) totalInput.value = '---';
        } else {
            dispositionInput.value = 'after updated';
            // Clear storage and total if they were "---"
            if (storageInput && storageInput.value === '---') storageInput.value = '';
            if (totalInput && totalInput.value === '---') totalInput.value = '';
        }
    }
}
</script>

</body>
</html>
