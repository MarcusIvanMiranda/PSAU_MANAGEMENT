<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit();
}

include "connect.php";
error_reporting(0);
$datatable = "records_document_main";
$results_per_page = 27;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$user_role = $_SESSION['role'];
$user_id   = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Released Documents - PSAU Records System</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="assets/css/psau-style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --green-950: #0d2b1e;
            --green-900: #1e5a3d;
            --green-800: #2d7a4f;
            --green-700: #3a9160;
            --green-600: #4aab72;
            --green-200: #a7d4b8;
            --green-100: #d4edde;
            --green-50:  #eef8f2;
            --gold:      #c9a84c;
            --white:     #ffffff;
            --gray-50:   #f8f9f8;
            --gray-100:  #f0f2f0;
            --gray-200:  #e2e5e2;
            --gray-300:  #cdd1cd;
            --gray-400:  #9bab9e;
            --gray-500:  #6d7d70;
            --gray-600:  #586a5c;
            --gray-700:  #3d4f40;
            --gray-900:  #1a2a1c;
        }
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: var(--gray-50); min-height: 100vh; color: var(--gray-700); }

        .bg-layer {
            position: fixed; inset: 0; z-index: 0;
            background: radial-gradient(ellipse 70% 50% at 5% 0%, rgba(30,90,61,0.08) 0%, transparent 55%),
                        radial-gradient(ellipse 50% 40% at 95% 100%, rgba(74,171,114,0.06) 0%, transparent 50%), var(--gray-50);
        }
        .bg-grid {
            position: fixed; inset: 0; z-index: 0;
            background-image: linear-gradient(rgba(30,90,61,0.035) 1px, transparent 1px), linear-gradient(90deg, rgba(30,90,61,0.035) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        .page { position: relative; z-index: 1; max-width: 1400px; margin: 0 auto; padding: 32px 24px 56px; }

        /* ── Header ── */
        .page-header {
            background: linear-gradient(145deg, var(--green-950) 0%, var(--green-900) 55%, var(--green-800) 100%);
            border-radius: 18px; padding: 32px 40px; margin-bottom: 24px;
            position: relative; overflow: hidden;
            box-shadow: 0 20px 56px rgba(14,43,30,0.22), 0 4px 12px rgba(14,43,30,0.14);
        }
        .page-header::before {
            content: ''; position: absolute; width: 420px; height: 420px; border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.06); top: -180px; right: -100px; pointer-events: none;
        }
        .header-noise {
            position: absolute; inset: 0; pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            opacity: 0.35;
        }
        .header-inner { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
        .header-eyebrow { font-size: 0.6875rem; font-weight: 600; letter-spacing: 0.16em; text-transform: uppercase; color: rgba(201,168,76,0.85); margin-bottom: 6px; }
        .header-title { font-family: 'Playfair Display', serif; font-size: clamp(1.5rem,3vw,2.125rem); font-weight: 700; color: #fff; line-height: 1.15; }
        .header-sub { font-size: 0.875rem; color: rgba(255,255,255,0.5); font-weight: 300; margin-top: 4px; }
        .header-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 14px; border-radius: 999px;
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);
            font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.75);
            backdrop-filter: blur(4px); margin-top: 12px;
        }
        .header-pill-dot { width: 7px; height: 7px; border-radius: 50%; background: #4ade80; box-shadow: 0 0 0 2px rgba(74,222,128,0.3); }

        /* ── Stats ── */
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 20px; }
        .stat-card {
            background: var(--white); border-radius: 14px; border: 1px solid var(--gray-200);
            padding: 20px 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: box-shadow 0.2s ease;
        }
        .stat-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.09); }
        .stat-label { font-size: 0.7rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--gray-400); margin-bottom: 8px; }
        .stat-value { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: var(--green-900); line-height: 1; }

        /* ── Search ── */
        .search-card {
            background: var(--white); border-radius: 14px; border: 1px solid var(--gray-200);
            padding: 20px 24px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .search-form { display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; }
        .search-group { flex: 1; min-width: 220px; }
        .search-label { display: block; margin-bottom: 6px; font-size: 0.8125rem; font-weight: 600; color: var(--gray-700); }
        .search-input {
            display: block; width: 100%; padding: 9px 14px;
            font-family: 'DM Sans', sans-serif; font-size: 0.875rem;
            color: var(--gray-900); background: var(--white);
            border: 1px solid var(--gray-200); border-radius: 9px;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .search-input:focus { outline: none; border-color: var(--green-700); box-shadow: 0 0 0 3px rgba(30,90,61,0.1); }
        .search-input::placeholder { color: var(--gray-300); }
        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 20px; border-radius: 9px; border: none; font-family: 'DM Sans', sans-serif; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease; }
        .btn-search { background: var(--green-900); color: #fff; box-shadow: 0 2px 8px rgba(30,90,61,0.25); }
        .btn-search:hover { background: var(--green-800); transform: translateY(-1px); }
        .btn-clear { background: var(--gray-100); color: var(--gray-600); border: 1px solid var(--gray-200); text-decoration: none; }
        .btn-clear:hover { background: var(--gray-200); }

        /* ── Table Card ── */
        .table-card {
            background: var(--white); border-radius: 14px; border: 1px solid var(--gray-200);
            overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .table-card-head {
            padding: 16px 24px; border-bottom: 1px solid var(--gray-100);
            display: flex; justify-content: space-between; align-items: center;
            background: var(--gray-50);
        }
        .table-card-title { font-size: 0.875rem; font-weight: 600; color: var(--gray-900); }
        .table-scope { font-size: 0.75rem; color: var(--gray-400); }

        /* ── Data Table ── */
        .data-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        .data-table thead th {
            padding: 11px 14px; text-align: left;
            font-size: 0.7rem; font-weight: 600; letter-spacing: 0.09em; text-transform: uppercase;
            color: var(--gray-400); background: var(--gray-50); border-bottom: 1px solid var(--gray-200);
            white-space: nowrap;
        }
        .data-table tbody td { padding: 13px 14px; border-bottom: 1px solid var(--gray-100); vertical-align: middle; }
        .data-table tbody tr:last-child td { border-bottom: none; }
        .data-table tbody tr { transition: background 0.15s; }
        .data-table tbody tr:hover td { background: var(--green-50); }

        /* ── Badge ── */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 999px;
            font-size: 0.7rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase;
        }
        .badge-released { background: var(--green-50); color: #166534; border: 1px solid var(--green-200); }
        .badge-dot { width: 5px; height: 5px; border-radius: 50%; background: #22c55e; }

        /* ── Serial ── */
        .serial {
            font-family: 'Courier New', monospace; font-size: 0.75rem; font-weight: 700;
            background: var(--gray-100); color: var(--green-900);
            padding: 3px 9px; border-radius: 6px; letter-spacing: 0.06em;
        }

        /* ── Action buttons ── */
        .btn-act {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 12px; border-radius: 7px; border: 1px solid var(--gray-200);
            background: var(--white); color: var(--gray-600);
            font-family: 'DM Sans', sans-serif; font-size: 0.75rem; font-weight: 600;
            cursor: pointer; text-decoration: none; transition: all 0.18s ease;
        }
        .btn-act:hover { transform: translateY(-1px); box-shadow: 0 3px 8px rgba(0,0,0,0.1); }
        .btn-act.track { background: var(--green-900); color: #fff; border-color: var(--green-900); }
        .btn-act.track:hover { background: var(--green-800); }
        .btn-act svg { width: 12px; height: 12px; }

        /* ── Pagination ── */
        .pagination { padding: 16px 24px; border-top: 1px solid var(--gray-100); display: flex; justify-content: center; align-items: center; gap: 6px; flex-wrap: wrap; }
        .page-link {
            padding: 6px 12px; border: 1px solid var(--gray-200); background: var(--white);
            color: var(--gray-600); text-decoration: none; border-radius: 8px;
            font-size: 0.8125rem; font-family: 'DM Sans', sans-serif; font-weight: 500;
            transition: all 0.18s ease;
        }
        .page-link:hover { background: var(--gray-50); border-color: var(--gray-300); }
        .page-link.active { background: var(--green-900); color: #fff; border-color: var(--green-900); }

        /* ── Empty State ── */
        .empty-state { text-align: center; padding: 64px 24px; }
        .empty-icon { font-size: 3rem; opacity: 0.3; margin-bottom: 14px; }
        .empty-title { font-size: 1.0625rem; font-weight: 600; color: var(--gray-700); margin-bottom: 6px; }
        .empty-text  { font-size: 0.875rem; color: var(--gray-400); margin-bottom: 20px; }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--green-600); border-radius: 99px; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .page { padding: 20px 14px 40px; }
            .page-header { padding: 24px 20px; }
            .stats-row { gap: 10px; }
            .stat-value { font-size: 1.5rem; }
            .search-form { flex-direction: column; }
            .search-group { width: 100%; }
            .data-table { font-size: 0.8rem; }
            .data-table thead th, .data-table tbody td { padding: 9px 10px; }
            .data-table th:nth-child(8), .data-table td:nth-child(8),
            .data-table th:nth-child(9), .data-table td:nth-child(9),
            .data-table th:nth-child(10),.data-table td:nth-child(10) { display: none; }
        }
        @media (max-width: 480px) {
            .stats-row { grid-template-columns: 1fr 1fr; }
            .stats-row .stat-card:last-child { grid-column: span 2; }
        }
    </style>
</head>
<body>
<div class="bg-layer"></div>
<div class="bg-grid"></div>

<div class="page">

    <!-- Header -->
    <div class="page-header">
        <div class="header-noise"></div>
        <div class="header-inner">
            <div>
                <div class="header-eyebrow">Document Tracking System</div>
                <h1 class="header-title">Released Documents</h1>
                <p class="header-sub">Documents that have been successfully released</p>
                <div class="header-pill">
                    <span class="header-pill-dot"></span>
                    Completed
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <?php
    $delivered = 'RELEASED';
    if ($user_role === 'admin') {
        $total_sql = "SELECT COUNT(*) AS c FROM $datatable WHERE document_status='$delivered'";
        $month_sql = "SELECT COUNT(*) AS c FROM $datatable WHERE document_status='$delivered' AND MONTH(date_added)=MONTH(CURRENT_DATE) AND YEAR(date_added)=YEAR(CURRENT_DATE)";
        $user_sql  = "SELECT COUNT(*) AS c FROM $datatable WHERE document_status='$delivered' AND added_by=$user_id";
    } else {
        $total_sql = "SELECT COUNT(*) AS c FROM $datatable WHERE document_status='$delivered' AND added_by=$user_id";
        $month_sql = "SELECT COUNT(*) AS c FROM $datatable WHERE document_status='$delivered' AND added_by=$user_id AND MONTH(date_added)=MONTH(CURRENT_DATE) AND YEAR(date_added)=YEAR(CURRENT_DATE)";
        $user_sql  = $total_sql;
    }
    $total_count = $conn->query($total_sql)->fetch_assoc()['c'];
    $month_count = $conn->query($month_sql)->fetch_assoc()['c'];
    $user_count  = $conn->query($user_sql)->fetch_assoc()['c'];
    ?>
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Total Released</div>
            <div class="stat-value"><?= $total_count ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">This Month</div>
            <div class="stat-value"><?= $month_count ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Your Documents</div>
            <div class="stat-value"><?= $user_count ?></div>
        </div>
    </div>

    <!-- Search -->
    <div class="search-card">
        <form method="GET" class="search-form">
            <div class="search-group">
                <label class="search-label">Search Documents</label>
                <input type="text" name="filtertext" class="search-input"
                       placeholder="Document title or type…"
                       value="<?= htmlspecialchars($_GET['filtertext'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-search">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                Search
            </button>
            <?php if (!empty($_GET['filtertext'])): ?>
                <a href="viewdelivered.php" class="btn btn-clear">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-card-head">
            <span class="table-card-title">Released Documents</span>
            <span class="table-scope"><?= $user_role==='admin' ? 'Showing all users' : 'Your documents only' ?></span>
        </div>

        <?php
        $filtertext  = trim($_GET['filtertext'] ?? '');
        $page        = max(1, (int)($_GET['page'] ?? 1));
        $start_from  = ($page - 1) * $results_per_page;

        if ($user_role === 'admin') {
            $sql       = "SELECT * FROM $datatable WHERE (document_title LIKE '%$filtertext%' OR document_type LIKE '%$filtertext%') AND document_status='$delivered' ORDER BY date_added DESC LIMIT $start_from, $results_per_page";
            $count_sql = "SELECT COUNT(*) AS total FROM $datatable WHERE (document_title LIKE '%$filtertext%' OR document_type LIKE '%$filtertext%') AND document_status='$delivered'";
        } else {
            $sql       = "SELECT * FROM $datatable WHERE (document_title LIKE '%$filtertext%' OR document_type LIKE '%$filtertext%') AND document_status='$delivered' AND added_by=$user_id ORDER BY date_added DESC LIMIT $start_from, $results_per_page";
            $count_sql = "SELECT COUNT(*) AS total FROM $datatable WHERE (document_title LIKE '%$filtertext%' OR document_type LIKE '%$filtertext%') AND document_status='$delivered' AND added_by=$user_id";
        }

        $rs_result   = $conn->query($sql);
        $count_row   = $conn->query($count_sql)->fetch_assoc();
        $total_pages = ceil($count_row["total"] / $results_per_page);
        ?>

        <div style="overflow-x:auto;">
        <?php if ($rs_result->num_rows > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Actions</th>
                        <th>Rec #</th>
                        <th>Document Title</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Serial Code</th>
                        <th>Date Added</th>
                        <th>Received From</th>
                        <th>Delivered To</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $rs_result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <form action="details.php" method="POST" style="display:inline;">
                                <button type="submit" name="RefID" value="<?= $row['serial_code'] ?>" class="btn-act track">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Track
                                </button>
                            </form>
                        </td>
                        <td style="color:var(--gray-400);font-size:0.8125rem;"><?= $row['idrecords_document_main'] ?></td>
                        <td style="font-weight:600;color:var(--gray-900);max-width:200px;"><?= htmlspecialchars($row['document_title']) ?></td>
                        <td style="color:var(--gray-600);font-size:0.8125rem;"><?= htmlspecialchars($row['document_type']) ?></td>
                        <td><span class="badge badge-released"><span class="badge-dot"></span><?= htmlspecialchars($row['document_status']) ?></span></td>
                        <td><span class="serial"><?= htmlspecialchars($row['serial_code']) ?></span></td>
                        <td style="color:var(--gray-500);font-size:0.8125rem;white-space:nowrap;"><?= date('M d, Y H:i', strtotime($row['date_added'])) ?></td>
                        <td style="font-size:0.8125rem;"><?= htmlspecialchars($row['received_from'] . ' — ' . $row['employee_receipt']) ?></td>
                        <td style="font-size:0.8125rem;"><?= htmlspecialchars($row['delivered_to']) ?></td>
                        <td style="font-size:0.8125rem;color:var(--gray-500);"><?= htmlspecialchars($row['document_remarks']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">✅</div>
                <div class="empty-title"><?= !empty($filtertext) ? 'No results found' : 'No released documents yet' ?></div>
                <div class="empty-text"><?= !empty($filtertext) ? 'Try adjusting your search terms.' : 'No documents have been released yet.' ?></div>
                <?php if (!empty($filtertext)): ?>
                    <a href="viewdelivered.php" class="btn btn-search" style="display:inline-flex;text-decoration:none;">Clear Search</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            $cf = !empty($filtertext) ? "&filtertext=".urlencode($filtertext) : "";
            if ($page > 1) echo "<a href='viewdelivered.php?page=".($page-1)."$cf' class='page-link'>← Prev</a>";
            for ($i = 1; $i <= $total_pages; $i++) echo "<a href='viewdelivered.php?page=$i$cf' class='page-link ".($i==$page?'active':'')."'>$i</a>";
            if ($page < $total_pages) echo "<a href='viewdelivered.php?page=".($page+1)."$cf' class='page-link'>Next →</a>";
            ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
setTimeout(() => window.location.reload(), 30000);
</script>
</body>
</html>
<?php $conn->close(); ?>