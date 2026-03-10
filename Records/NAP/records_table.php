<?php
require_once '../connect.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle section filtering
$section_filter = $_GET['section_filter'] ?? '';
$sql = "SELECT * FROM nap_records";
if (!empty($section_filter)) {
    $sql .= " WHERE section_unit = '" . $conn->real_escape_string($section_filter) . "'";
}
$sql .= " ORDER BY created_at DESC";
$records = $conn->query($sql);

// Get unique sections for filter dropdown (same as index.php)
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
    <title>NAP Records - Data View</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            background: #525659;
            padding: 20px;
        }

        /* ── Page wrapper (landscape A4 proportions) ── */
        .page {
            background: white;
            width: 1200px;
            margin: 0 auto;
            padding: 18px 18px 14px 18px;
            box-shadow: 0 0 12px rgba(0,0,0,0.55);
        }

        /* ── Small caption above the form ── */
        .form-caption {
            font-size: 7pt;
            margin-bottom: 4px;
        }

        /* ════════════════════════════
           HEADER (logo + fields grid)
           ════════════════════════════ */
        .header-wrap {
            display: grid;
            /* NAP box | field area */
            grid-template-columns: 210px 1fr;
            border: 1.5px solid #000;
        }

        /* Left: NAP title box */
        .nap-title-box {
            border-right: 1.5px solid #000;
            padding: 8px 10px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        /* Inner box that wraps ALL three lines */
        .nap-inner-box {
            border: 1.5px solid #000;
            padding: 8px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        .nap-title-box .nap-main { font-size: 9.5pt; font-weight: bold; line-height: 1.3; }
        .nap-title-box .nap-sub  { font-size: 7.5pt; font-style: italic; margin: 3px 0 8px 0; }
        .nap-title-box .nap-form-label {
            font-size: 8.5pt;
            font-weight: bold;
            display: inline-block;
            text-align: center;
            line-height: 1.3;
        }

        /* Right: Office fields */
        .fields-area {
            display: grid;
            /* 3 columns for the main rows */
            grid-template-columns: 2.4fr 1.8fr 1fr;
        }

        /* Each field cell */
        .f-cell {
            border-bottom: 1px solid #000;
            border-right: 1px solid #000;
            padding: 3px 5px;
            font-size: 7pt;
            min-height: 28px;
        }
        .f-cell:last-child,
        .f-cell.no-right { border-right: none; }
        .f-cell.no-bottom { border-bottom: none; }
        .f-cell .lbl { font-weight: bold; font-size: 6.5pt; display: block; margin-bottom: 1px; }
        .f-cell .val { font-size: 8pt; }

        /* Row that spans 2 columns (Name of Office) */
        .f-span2 { grid-column: span 2; }
        .f-span3 { grid-column: span 3; }
        .f-row2  { grid-row: span 2; }

        /* ════════════════════════════
           MAIN TABLE
           ════════════════════════════ */
        .nap-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border-left: 1.5px solid #000;
            border-right: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
        }
        .nap-table th,
        .nap-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            font-size: 6.8pt;
            vertical-align: top;
            text-align: center;
            overflow: hidden;
        }
        .nap-table th {
            background: #f0f0f0;
            font-weight: bold;
            line-height: 1.15;
            vertical-align: middle;
        }
        .nap-table td { min-height: 18px; height: 18px; }

        /* Column widths */
        .col-title  { width: 17%; }
        .col-period { width: 8%; }
        .col-vol    { width: 6%; }
        .col-med    { width: 7%; }
        .col-rest   { width: 8%; }
        .col-loc    { width: 9%; }
        .col-freq   { width: 6%; }
        .col-dup    { width: 6%; }
        .col-time   { width: 5%; }
        .col-util   { width: 6%; }
        .col-ret    { width: 12%; }
        .col-disp   { width: 10%; }

        /* Retention sub-header */
        .ret-sub {
            display: flex;
            border-top: 1px solid #000;
            margin: 3px -3px -2px -3px;
        }
        .ret-sub span {
            flex: 1;
            border-right: 1px solid #000;
            padding: 2px 0;
            font-size: 6.5pt;
            font-weight: bold;
            text-align: center;
        }
        .ret-sub span:last-child { border-right: none; }

        /* Retention data cells */
        .ret-data {
            display: flex;
            margin: 0 -3px;
            height: 100%;
        }
        .ret-data span {
            flex: 1;
            border-right: 1px solid #000;
            padding: 1px 0;
            text-align: center;
        }
        .ret-data span:last-child { border-right: none; }

        td.title-cell { text-align: left; padding-left: 4px; }

        /* Page number */
        .page-num {
            font-size: 6.5pt;
            text-align: right;
            padding: 3px 5px 0 0;
        }

        /* ════════════════════════════
           FOOTER (legend + signatures)
           ════════════════════════════ */
        .footer-wrap {
            margin-top: 10px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 0;
        }
        .legend-block { font-size: 7pt; padding-right: 20px; }
        .legend-block .leg-title { font-weight: bold; font-size: 7.5pt; margin-bottom: 6px; text-decoration: underline; }
        .legend-block table { border-collapse: collapse; }
        .legend-block td { padding: 4px 16px 4px 0; font-size: 6.8pt; vertical-align: top; }
        .legend-block .leg-label { font-weight: bold; white-space: nowrap; padding-right: 24px; }

        .sig-block {
            font-size: 7.5pt;
            padding: 0 10px;
        }
        .sig-block .sig-heading {
            font-weight: bold;
            font-size: 7.5pt;
            margin-bottom: 24px;
        }
        .sig-block .sig-line {
            border-bottom: 1px solid #000;
            margin-bottom: 3px;
        }
        .sig-block .sig-name {
            font-weight: bold;
            font-size: 7.5pt;
            text-align: center;
        }
        .sig-block .sig-pos {
            font-size: 6.8pt;
            text-align: center;
        }

        /* ── Navigation buttons ── */
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
            background: #1a5276;
            border-radius: 3px;
            font-size: 13px;
        }
        .nav-bar a:hover {
            background: #154360;
        }
        .nav-bar button {
            padding: 6px 14px;
            background: #117a65;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13px;
        }

        /* ── Print rules ── */
        @media print {
            body { background: none; padding: 0; }
            .page { box-shadow: none; width: 100%; padding: 10px; }
            .nav-bar { display: none !important; }
            .filter-section { display: none !important; }
            @page { size: A4 landscape; margin: 8mm; }
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
    <a href="index.php">← Back to Entry Form</a>
    <button onclick="window.print()">🖨 Print Form</button>
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
            <button type="button" onclick="window.location.href='records_table.php'">Clear Filter</button>
        <?php endif; ?>
    </form>
    <?php if (!empty($section_filter)): ?>
        <span style="color: #666; font-size: 12px;">Showing records for: <strong><?= htmlspecialchars($section_filter) ?></strong></span>
    <?php endif; ?>
</div>

<?php
// Fetch the latest record to get header data
$header_sql = "SELECT name_of_office, department_division, section_unit, telephone_no, email_address, address, person_incharge, date_prepared FROM nap_records ORDER BY created_at DESC LIMIT 1";
$header_result = $conn->query($header_sql);
$header_data = $header_result ? $header_result->fetch_assoc() : [];
?>

<!-- ══════════════════════════════
     PRINTABLE FORM
     ══════════════════════════════ -->
<div class="page">
    <div class="form-caption">NAP Records Inventory and Appraisal Form<br>2023</div>

    <!-- ── HEADER ── -->
    <div class="header-wrap">

        <!-- NAP Logo / Title -->
        <div class="nap-title-box">
            <div class="nap-inner-box">
                <div class="nap-main">NATIONAL ARCHIVES OF THE PHILIPPINES</div>
                <div class="nap-sub">Pambansang Sinupan ng Pilipinas</div>
                <div class="nap-form-label">RECORDS INVENTORY AND APPRAISAL</div>
            </div>
        </div>

        <!-- Office fields: 3-column grid -->
        <div class="fields-area">
            <!-- Col 1: Name of Office spans rows 1+2 | Col 2-3: Dept/Division | Tel No -->
            <div class="f-cell f-row2">
                <span class="lbl">1. NAME OF OFFICE:</span>
                <span class="val"><?= htmlspecialchars($header_data['name_of_office'] ?? '') ?></span>
            </div>
            <div class="f-cell">
                <span class="lbl">2. DEPARTMENT/DIVISION:</span>
                <span class="val"><?= htmlspecialchars($header_data['department_division'] ?? '') ?></span>
            </div>
            <div class="f-cell no-right">
                <span class="lbl">4. TELEPHONE NO.:</span>
                <span class="val"><?= htmlspecialchars($header_data['telephone_no'] ?? '') ?></span>
            </div>

            <!-- Col 2-3 row 2: Section/Unit | Email -->
            <div class="f-cell">
                <span class="lbl">3. SECTION/UNIT:</span>
                <span class="val"><?= htmlspecialchars($header_data['section_unit'] ?? '') ?></span>
            </div>
            <div class="f-cell no-right">
                <span class="lbl">5. EMAIL ADDRESS:</span>
                <span class="val"><?= htmlspecialchars($header_data['email_address'] ?? '') ?></span>
            </div>

            <!-- Col 1: Address spans rows 3+4 | Col 2-3: Person-in-charge | Date Prepared -->
            <div class="f-cell f-row2 no-bottom">
                <span class="lbl">6. ADDRESS:</span>
                <span class="val"><?= htmlspecialchars($header_data['address'] ?? '') ?></span>
            </div>
            <div class="f-cell no-bottom">
                <span class="lbl">7. PERSON-IN-CHARGE OF FILES:</span>
                <span class="val"><?= htmlspecialchars($header_data['person_incharge'] ?? '') ?></span>
            </div>
            <div class="f-cell no-bottom no-right">
                <span class="lbl">8. DATE PREPARED:</span>
                <span class="val"><?= htmlspecialchars($header_data['date_prepared'] ?? '') ?></span>
            </div>
        </div>
    </div><!-- end header-wrap -->

    <!-- ── DATA TABLE ── -->
    <table class="nap-table">
        <thead>
            <tr>
                <th class="col-title">9. RECORDS SERIES TITLE AND DESCRIPTION</th>
                <th class="col-period">10. PERIOD COVERED / INCLUSIVE DATES</th>
                <th class="col-vol">11. VOLUME</th>
                <th class="col-med">12. RECORDS MEDIUM</th>
                <th class="col-rest">13. RESTRICTIONS</th>
                <th class="col-loc">14. LOCATION OF RECORDS</th>
                <th class="col-freq">15. FREQUENCY OF USE</th>
                <th class="col-dup">16. DUPLICATION</th>
                <th class="col-time">17. TIME VALUE (T/P)</th>
                <th class="col-util">18. UTILITY VALUE Adm/F/L/Arc</th>
                <th class="col-ret">
                    19. RETENTION PERIOD
                    <div class="ret-sub">
                        <span>Active</span>
                        <span>Storage</span>
                        <span>Total</span>
                    </div>
                </th>
                <th class="col-disp">20. DISPOSITION PROVISION</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $rowCount = 0;
            while ($row = $records->fetch_assoc()):
                $rowCount++;
            ?>
            <tr>
                <td class="title-cell">
                    <strong><?= htmlspecialchars($row['records_series_title']) ?></strong><br>
                    <span style="font-size:6.2pt;"><?= htmlspecialchars($row['records_description']) ?></span>
                </td>
                <td><?= htmlspecialchars($row['period_covered_from']) ?></td>
                <td><?= htmlspecialchars($row['volume']) ?></td>
                <td><?= htmlspecialchars($row['records_medium']) ?></td>
                <td><?= htmlspecialchars($row['restrictions']) ?></td>
                <td><?= htmlspecialchars($row['location_of_records']) ?></td>
                <td><?= htmlspecialchars($row['request_frequency']) ?></td>
                <td><?= htmlspecialchars($row['duplication_value']) ?></td>
                <td><?= htmlspecialchars($row['time_value']) ?></td>
                <td><?= htmlspecialchars($row['utility_value']) ?></td>
                <td style="padding:0;">
                    <div class="ret-data">
                        <span><?= htmlspecialchars($row['retention_period_active']) ?></span>
                        <span><?= htmlspecialchars($row['retention_period_storage']) ?></span>
                        <span><?= htmlspecialchars($row['retention_period_total']) ?></span>
                    </div>
                </td>
                <td><?= htmlspecialchars($row['disposition_provision']) ?></td>
            </tr>
            <?php endwhile; ?>

            <!-- Blank filler rows to always show at least 18 rows -->
            <?php for ($i = $rowCount; $i < 18; $i++): ?>
            <tr>
                <td class="title-cell">&nbsp;</td>
                <td></td><td></td><td></td><td></td><td></td>
                <td></td><td></td><td></td><td></td>
                <td style="padding:0;">
                    <div class="ret-data">
                        <span>&nbsp;</span><span></span><span></span>
                    </div>
                </td>
                <td></td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- ── FOOTER ── -->
    <div style="margin-top:12px;">

        <!-- Legend (full width, above signatures) -->
        <div class="legend-block" style="margin-bottom:16px;">
            <div class="leg-title">LEGEND:</div>
            <table>
                <tr>
                    <td class="leg-label">TIME VALUE:</td>
                    <td>T &nbsp;– &nbsp;Temporary</td>
                    <td style="padding-left:12px;">P &nbsp;– &nbsp;Permanent</td>
                </tr>
                <tr>
                    <td class="leg-label">UTILITY VALUE:</td>
                    <td>Adm – Administrative</td>
                    <td style="padding-left:12px;">F &nbsp;– &nbsp;Fiscal</td>
                </tr>
                <tr>
                    <td></td>
                    <td>L &nbsp;&nbsp;– &nbsp;Legal</td>
                    <td style="padding-left:12px;">Arc – Archival</td>
                </tr>
            </table>
        </div>

        <!-- Signatures row: Prepared By | Assisted By | Approved By -->
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0 20px;">
            <div class="sig-block" style="text-align:center;">
                <div class="sig-heading">PREPARED BY:</div>
                <div class="sig-line"></div>
                <div class="sig-name">ARTHUR S. AGUSTIN Information Technology Officer 1</div>
                <div class="sig-pos">Name and Position</div>
            </div>
            <div class="sig-block" style="text-align:center;">
                <div class="sig-heading">ASSISTED BY:</div>
                <div class="sig-line"></div>
                <div class="sig-pos">NAP Records Management Analyst</div>
            </div>
            <div class="sig-block" style="text-align:center;">
                <div class="sig-heading">APPROVED BY:</div>
                <div class="sig-line"></div>
                <div class="sig-pos">Chief of the Division/Department</div>
            </div>
        </div>
    </div>

    <div class="page-num">Page ___ of ___ Pages</div>
</div><!-- end .page -->

</body>
</html>