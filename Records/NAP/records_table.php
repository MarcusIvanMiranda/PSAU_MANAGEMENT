<?php
require_once '../connect.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$header_id = isset($_GET['header_id']) ? (int)$_GET['header_id'] : 0;

if ($header_id) {
    $header_result = $conn->query("SELECT * FROM nap_headers WHERE id = $header_id");
    $header_data   = $header_result ? $header_result->fetch_assoc() : [];
    $records_sql   = "SELECT * FROM nap_records WHERE header_id = $header_id ORDER BY created_at ASC";
} else {
    $header_result = $conn->query("SELECT * FROM nap_headers ORDER BY created_at DESC LIMIT 1");
    $header_data   = $header_result ? $header_result->fetch_assoc() : [];
    $records_sql   = "SELECT * FROM nap_records ORDER BY header_id ASC, created_at ASC";
}
$records = $conn->query($records_sql);

$all_hdrs_res = $conn->query("SELECT id, department_division, section_unit, date_prepared FROM nap_headers ORDER BY created_at DESC");
$all_hdrs = [];
if ($all_hdrs_res) while ($r = $all_hdrs_res->fetch_assoc()) $all_hdrs[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>NAP Records – Print View</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body { font-family: Arial, sans-serif; background: #525659; padding: 20px; }

/* ── NAV ── */
.nav {
    background: #fff; padding: 12px 20px;
    width: 1240px; margin: 0 auto 14px;
    border-radius: 6px; display: flex; gap: 10px; align-items: center;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}
.nav a, .nav button {
    padding: 7px 16px; font-size: 13px; font-weight: 600;
    border-radius: 6px; text-decoration: none; cursor: pointer;
    font-family: inherit; border: none; transition: all 0.15s;
}
.nav .btn-back  { background: #1a5c38; color: #fff; }
.nav .btn-back:hover { background: #145030; }
.nav .btn-print { background: #2d9e5f; color: #fff; }
.nav .btn-print:hover { background: #1e7a47; }
.nav label { font-size: 13px; font-weight: 600; color: #374a55; }
.nav select {
    padding: 6px 10px; border: 1.5px solid #c8d3db;
    border-radius: 6px; font-size: 13px; font-family: inherit; color: #1a2830;
}
.nav .ml { margin-left: auto; }

/* ── PAGE ── */
.page {
    background: white; width: 1240px;
    margin: 0 auto; padding: 18px;
    box-shadow: 0 0 14px rgba(0,0,0,0.5);
}
.form-caption { font-size: 7pt; margin-bottom: 4px; color: #555; }

/* ── HEADER ── */
.header-wrap { display: grid; grid-template-columns: 210px 1fr; border: 1.5px solid #000; }
.nap-box {
    border-right: 1.5px solid #000; padding: 8px 10px;
    text-align: center; display: flex; align-items: center; justify-content: center;
}
.nap-inner {
    border: 1.5px solid #000; padding: 8px 12px;
    display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%;
}
.nap-main  { font-size: 9.5pt; font-weight: bold; line-height: 1.3; }
.nap-sub   { font-size: 7.5pt; font-style: italic; margin: 3px 0 8px; }
.nap-label { font-size: 8.5pt; font-weight: bold; text-align: center; line-height: 1.3; }

.fields-area { display: grid; grid-template-columns: 2.4fr 1.8fr 1fr; }
.f-cell {
    border-bottom: 1px solid #000; border-right: 1px solid #000;
    padding: 3px 5px; font-size: 7pt; min-height: 28px;
}
.f-cell.no-right  { border-right: none; }
.f-cell.no-bottom { border-bottom: none; }
.f-cell .lbl { font-weight: bold; font-size: 6.5pt; display: block; margin-bottom: 1px; }
.f-cell .val { font-size: 8pt; }
.row2 { grid-row: span 2; }

/* ── TABLE ── */
.nap-table {
    width: 100%; border-collapse: collapse; table-layout: fixed;
    border-left: 1.5px solid #000; border-right: 1.5px solid #000; border-bottom: 1.5px solid #000;
}
.nap-table th, .nap-table td {
    border: 1px solid #000; padding: 2px 3px;
    font-size: 6.8pt; vertical-align: top; text-align: center; overflow: hidden;
}
.nap-table th { background: #f0f0f0; font-weight: bold; line-height: 1.15; vertical-align: middle; }
.nap-table td { min-height: 18px; height: 18px; }

.col-title  { width: 17%; } .col-period { width: 8%; } .col-vol  { width: 6%; }
.col-med    { width: 7%;  } .col-rest   { width: 8%; } .col-loc  { width: 9%; }
.col-freq   { width: 6%;  } .col-dup    { width: 6%; } .col-time { width: 5%; }
.col-util   { width: 6%;  } .col-ret    { width: 12%;} .col-disp { width: 10%;}

.ret-sub { display: flex; border-top: 1px solid #000; margin: 3px -3px -2px -3px; }
.ret-sub span { flex: 1; border-right: 1px solid #000; padding: 2px 0; font-size: 6.5pt; font-weight: bold; text-align: center; }
.ret-sub span:last-child { border-right: none; }
.ret-data { display: flex; margin: 0 -3px; height: 100%; }
.ret-data span { flex: 1; border-right: 1px solid #000; padding: 1px 0; text-align: center; }
.ret-data span:last-child { border-right: none; }
td.title-cell { text-align: left; padding-left: 4px; }
.page-num { font-size: 6.5pt; text-align: right; padding: 3px 5px 0; }

/* ── FOOTER ── */
.leg { font-size: 7pt; }
.leg-title { font-weight: bold; font-size: 7.5pt; margin-bottom: 6px; text-decoration: underline; }
.leg table { border-collapse: collapse; }
.leg td { padding: 4px 16px 4px 0; font-size: 6.8pt; vertical-align: top; }
.leg .lk { font-weight: bold; white-space: nowrap; padding-right: 24px; }
.sig { font-size: 7.5pt; padding: 0 10px; text-align: center; }
.sig .sh { font-weight: bold; margin-bottom: 24px; }
.sig .sl { border-bottom: 1px solid #000; margin-bottom: 3px; }
.sig .sn { font-weight: bold; }
.sig .sp { font-size: 6.8pt; }

@media print {
    body { background: none; padding: 0; }
    .page { box-shadow: none; width: 100%; padding: 10px; }
    .nav { display: none !important; }
    @page { size: A4 landscape; margin: 8mm; }
}
</style>
</head>
<body>

<div class="nav">
    <a href="index.php" class="btn-back">← Back to Entry Form</a>
    <button class="btn-print" onclick="window.print()">🖨 Print / Save PDF</button>
    <label class="ml" for="hsel">View Header:</label>
    <select id="hsel" onchange="if(this.value!==undefined)location='records_table.php'+(this.value?'?header_id='+this.value:'')">
        <option value="">All Records</option>
        <?php foreach ($all_hdrs as $h): ?>
            <option value="<?= $h['id'] ?>" <?= $header_id == $h['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($h['department_division']) ?> – <?= htmlspecialchars($h['section_unit']) ?> (<?= $h['date_prepared'] ?>)
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="page">
    <div class="form-caption">NAP Records Inventory and Appraisal Form — 2023</div>

    <!-- HEADER -->
    <div class="header-wrap">
        <div class="nap-box">
            <div class="nap-inner">
                <div class="nap-main">NATIONAL ARCHIVES OF THE PHILIPPINES</div>
                <div class="nap-sub">Pambansang Sinupan ng Pilipinas</div>
                <div class="nap-label">RECORDS INVENTORY AND APPRAISAL</div>
            </div>
        </div>
        <div class="fields-area">
            <div class="f-cell row2">
                <span class="lbl">1. NAME OF OFFICE:</span>
                <span class="val"><?= htmlspecialchars($header_data['name_of_office'] ?? '') ?></span>
            </div>
            <div class="f-cell"><span class="lbl">2. DEPARTMENT/DIVISION:</span><span class="val"><?= htmlspecialchars($header_data['department_division'] ?? '') ?></span></div>
            <div class="f-cell no-right"><span class="lbl">4. TELEPHONE NO.:</span><span class="val"><?= htmlspecialchars($header_data['telephone_no'] ?? '') ?></span></div>
            <div class="f-cell"><span class="lbl">3. SECTION/UNIT:</span><span class="val"><?= htmlspecialchars($header_data['section_unit'] ?? '') ?></span></div>
            <div class="f-cell no-right"><span class="lbl">5. EMAIL ADDRESS:</span><span class="val"><?= htmlspecialchars($header_data['email_address'] ?? '') ?></span></div>
            <div class="f-cell row2 no-bottom"><span class="lbl">6. ADDRESS:</span><span class="val"><?= htmlspecialchars($header_data['address'] ?? '') ?></span></div>
            <div class="f-cell no-bottom"><span class="lbl">7. PERSON-IN-CHARGE OF FILES:</span><span class="val"><?= htmlspecialchars($header_data['person_incharge'] ?? '') ?></span></div>
            <div class="f-cell no-bottom no-right"><span class="lbl">8. DATE PREPARED:</span><span class="val"><?= htmlspecialchars($header_data['date_prepared'] ?? '') ?></span></div>
        </div>
    </div>

    <!-- TABLE -->
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
                <th class="col-ret">19. RETENTION PERIOD
                    <div class="ret-sub"><span>Active</span><span>Storage</span><span>Total</span></div>
                </th>
                <th class="col-disp">20. DISPOSITION PROVISION</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $cnt = 0;
            if ($records) while ($row = $records->fetch_assoc()): $cnt++;
            ?>
            <tr>
                <td class="title-cell"><strong><?= htmlspecialchars($row['records_series_title']) ?></strong><br>
                    <span style="font-size:6.2pt;"><?= htmlspecialchars($row['records_description']) ?></span></td>
                <td><?= htmlspecialchars($row['period_covered_from']) ?></td>
                <td><?= htmlspecialchars($row['volume']) ?></td>
                <td><?= htmlspecialchars($row['records_medium']) ?></td>
                <td><?= htmlspecialchars($row['restrictions']) ?></td>
                <td><?= htmlspecialchars($row['location_of_records']) ?></td>
                <td><?= htmlspecialchars($row['request_frequency']) ?></td>
                <td><?= htmlspecialchars($row['duplication_value']) ?></td>
                <td><?= htmlspecialchars($row['time_value']) ?></td>
                <td><?= htmlspecialchars($row['utility_value']) ?></td>
                <td style="padding:0;"><div class="ret-data">
                    <span><?= htmlspecialchars($row['retention_period_active']) ?></span>
                    <span><?= htmlspecialchars($row['retention_period_storage']) ?></span>
                    <span><?= htmlspecialchars($row['retention_period_total']) ?></span>
                </div></td>
                <td><?= htmlspecialchars($row['disposition_provision']) ?></td>
            </tr>
            <?php endwhile; ?>
            <?php for ($i = $cnt; $i < 18; $i++): ?>
            <tr>
                <td class="title-cell">&nbsp;</td><td></td><td></td><td></td><td></td><td></td>
                <td></td><td></td><td></td><td></td>
                <td style="padding:0;"><div class="ret-data"><span>&nbsp;</span><span></span><span></span></div></td>
                <td></td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- FOOTER -->
    <div style="margin-top:12px;">
        <div class="leg" style="margin-bottom:16px;">
            <div class="leg-title">LEGEND:</div>
            <table><tr>
                <td class="lk">TIME VALUE:</td>
                <td>T – Temporary</td><td style="padding-left:12px;">P – Permanent</td>
            </tr><tr>
                <td class="lk">UTILITY VALUE:</td>
                <td>Adm – Administrative</td><td style="padding-left:12px;">F – Fiscal</td>
            </tr><tr>
                <td></td><td>L – Legal</td><td style="padding-left:12px;">Arc – Archival</td>
            </tr></table>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0 20px;">
            <div class="sig"><div class="sh">PREPARED BY:</div><div class="sl"></div>
                <div class="sn"><?= htmlspecialchars($header_data['person_incharge'] ?? 'ARTHUR S. AGUSTIN Information Technology Officer 1') ?></div>
                <div class="sp">Name and Position</div></div>
            <div class="sig"><div class="sh">ASSISTED BY:</div><div class="sl"></div>
                <div class="sp">NAP Records Management Analyst</div></div>
            <div class="sig"><div class="sh">APPROVED BY:</div><div class="sl"></div>
                <div class="sp">Chief of the Division/Department</div></div>
        </div>
    </div>
    <div class="page-num">Page ___ of ___ Pages</div>
</div>

</body>
</html>