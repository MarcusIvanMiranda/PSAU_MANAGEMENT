<?php
session_start();
if (!isset($_SESSION['property_loggedin']) || $_SESSION['property_loggedin'] !== true) {
    header("location: login.php");
    exit;
}
include "connect.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);
//$servername = "";
//$username = "";
//$password = "";
//$dbname = "";
$datatable = "property_list"; // MySQL table name
$results_per_page = 20; // number of results per page
 
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$filtertext="";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSAU Property Management System</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <img src="PSAU_10.jpg" alt="PSAU Logo" class="header-logo">
            <div class="header-title">
                <h1>PAMPANGA STATE AGRICULTURAL UNIVERSITY</h1>
                <h2>Property Management System</h2>
            </div>
            <div class="header-user">
                <div style="text-align: right; margin-bottom: 0.5rem;">
                    <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['property_full_name']); ?></span>
                    <?php if (!empty($_SESSION['property_office'])): ?>
                        <div class="user-role">
                            🏢 <?php echo htmlspecialchars($_SESSION['property_office']); ?>
                            <?php if (!empty($_SESSION['property_members'])): ?>
                                | 👑 <?php echo htmlspecialchars($_SESSION['property_members']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($_SESSION['property_role'] === 'admin'): ?>
                    <a href="manage_accounts.php" class="btn btn-primary" style="margin-right: 0.5rem;">👥 Manage Accounts</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <section class="search-section">
            <form class="search-form" action='index.php' method='GET'>
                <input 
                    type="text" 
                    name="filtertext" 
                    class="search-input" 
                    placeholder="Search by Property No, Tag, Item, or Description..." 
                    value="<?php echo isset($_GET['filtertext']) ? htmlspecialchars($_GET['filtertext']) : ''; ?>"
                >
                <button type="submit" class="btn btn-primary">
                    🔍 Search Properties
                </button>
            </form>
        </section>

<?php
$filtertext = isset($_GET['filtertext']) ? trim($_GET['filtertext']) : '';
if (isset($_GET["page"])) { $page = $_GET["page"]; } else { $page=1; };
$start_from = ($page-1) * $results_per_page;

// Build search condition
$search_condition = "";
if (!empty($filtertext)) {
    $search_condition = " WHERE (property_no LIKE '%$filtertext%' OR property_tag LIKE '%$filtertext%' OR property_item LIKE '%$filtertext%' OR property_description LIKE '%$filtertext%')";
}

$sql = "SELECT * FROM ".$datatable.$search_condition." ORDER BY property_no DESC LIMIT $start_from, ".$results_per_page;
$rs_result = $conn->query($sql);

// Get total count for pagination
$count_sql = "SELECT COUNT(*) AS total FROM ".$datatable.$search_condition;
$result = $conn->query($count_sql);
$row = $result->fetch_assoc();
$total_pages = ceil($row["total"] / $results_per_page);
?>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            // Previous button
            if ($page > 1):
            ?>
                <a href='index.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $page - 1; ?>' class="nav-btn">
                    &lt;
                </a>
            <?php endif; ?>
            
            <?php
            // Calculate page range (show max 10 pages)
            $max_pages = 10;
            $start_page = max(1, $page - floor($max_pages / 2));
            $end_page = min($total_pages, $start_page + $max_pages - 1);
            
            // Adjust start page if we're near the end
            if ($end_page - $start_page < $max_pages - 1) {
                $start_page = max(1, $end_page - $max_pages + 1);
            }
            
            // Show first page if not in range
            if ($start_page > 1):
            ?>
                <a href='index.php?filtertext=<?php echo urlencode($filtertext); ?>&page=1' 
                   class='<?php if (1==$page) echo "curPage"; ?>'>
                    1
                </a>
                <?php if ($start_page > 2): ?>
                    <span class="pagination-dots">...</span>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php
            // Show page range
            for ($i=$start_page; $i<=$end_page; $i++):
            ?>
                <a href='index.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $i; ?>' 
                   class='<?php if ($i==$page) echo "curPage"; ?>'>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php
            // Show last page if not in range
            if ($end_page < $total_pages):
                if ($end_page < $total_pages - 1):
            ?>
                    <span class="pagination-dots">...</span>
                <?php endif; ?>
                <a href='index.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $total_pages; ?>' 
                   class='<?php if ($total_pages==$page) echo "curPage"; ?>'>
                    <?php echo $total_pages; ?>
                </a>
            <?php endif; ?>
            
            <?php
            // Next button
            if ($page < $total_pages):
            ?>
                <a href='index.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $page + 1; ?>' class="nav-btn">
                    &gt;
                </a>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>


        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 120px;">Actions</th>
                        <th>Property #</th>
                        <th>Property Tag</th>
                        <th>Item</th>
                        <th>Description</th>
                        <th>Serial Number</th>
                        <th>Value</th>
                        <th>Acquisition Date</th>
                        <th style="text-align: center;">Accountable Person</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rs_result && $rs_result->num_rows > 0): ?>
                        <?php while($row = $rs_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="action-buttons">
                                        <form action='printqrnow.php' method='POST' target='_blank' style="display: inline;">
                                            <button type='submit' name='RefID' value='<?php echo $row["property_tag"]; ?>' class="btn btn-success btn-sm">
                                                 Print QR
                                            </button>
                                        </form>
                                        <form action='propertydocument.php' method='GET' style="display: inline;">
                                            <button type='submit' name='filtertext' value='<?php echo $row["property_tag"]; ?>' class="btn btn-primary btn-sm">
                                                 View
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row["property_no"]); ?></td>
                                <td><strong><?php echo htmlspecialchars($row["property_tag"]); ?></strong></td>
                                <td><?php echo htmlspecialchars($row["property_item"]); ?></td>
                                <td><?php echo htmlspecialchars($row["property_description"]); ?></td>
                                <td><?php echo htmlspecialchars($row["property_serial_number"] ?? ''); ?></td>
                                <td>₱<?php 
                                    $propertyValue = $row["property_value"] ?? '0';
                                    $cleanedValue = str_replace([',', ' '], '', $propertyValue); // Remove commas and spaces
                                    if (is_numeric($cleanedValue)) {
                                        echo number_format((float)$cleanedValue, 2); 
                                    } else {
                                        echo htmlspecialchars($propertyValue); // Display as is if not a valid number
                                    }
                                ?></td>
                                <td><?php echo htmlspecialchars($row["property_acquisition_date"] ?? ''); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row["property_accountable_person"] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row["property_status"] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row["property_remarks"] ?? ''); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan='11' style='text-align: center; padding: 2rem;'>
                                <p style='color: var(--gray-500); font-size: 1.1rem;'>
                                    <?php if (!empty($filtertext)): ?>
                                        No properties found matching "<strong><?php echo htmlspecialchars($filtertext); ?></strong>"
                                    <?php else: ?>
                                        No properties found in the system.
                                    <?php endif; ?>
                                </p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            // Previous button
            if ($page > 1):
            ?>
                <a href='index.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $page - 1; ?>' class="nav-btn">
                    &lt;
                </a>
            <?php endif; ?>
            
            <?php
            // Calculate page range (show max 10 pages)
            $max_pages = 10;
            $start_page = max(1, $page - floor($max_pages / 2));
            $end_page = min($total_pages, $start_page + $max_pages - 1);
            
            // Adjust start page if we're near the end
            if ($end_page - $start_page < $max_pages - 1) {
                $start_page = max(1, $end_page - $max_pages + 1);
            }
            
            // Show first page if not in range
            if ($start_page > 1):
            ?>
                <a href='index.php?filtertext=<?php echo urlencode($filtertext); ?>&page=1' 
                   class='<?php if (1==$page) echo "curPage"; ?>'>
                    1
                </a>
                <?php if ($start_page > 2): ?>
                    <span class="pagination-dots">...</span>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php
            // Show page range
            for ($i=$start_page; $i<=$end_page; $i++):
            ?>
                <a href='index.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $i; ?>' 
                   class='<?php if ($i==$page) echo "curPage"; ?>'>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php
            // Show last page if not in range
            if ($end_page < $total_pages):
                if ($end_page < $total_pages - 1):
            ?>
                    <span class="pagination-dots">...</span>
                <?php endif; ?>
                <a href='index.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $total_pages; ?>' 
                   class='<?php if ($total_pages==$page) echo "curPage"; ?>'>
                    <?php echo $total_pages; ?>
                </a>
            <?php endif; ?>
            
            <?php
            // Next button
            if ($page < $total_pages):
            ?>
                <a href='index.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $page + 1; ?>' class="nav-btn">
                    &gt;
                </a>
            <?php endif; ?>
       
        </div>
        <?php endif; ?>
    </div>

    <footer style="text-align: center; padding: 2rem; color: var(--gray-500); margin-top: 3rem;">
        <p>&copy; <?php echo date('Y'); ?> PAMPANGA STATE AGRICULTURAL UNIVERSITY - Property Management System</p>
    </footer>
</body>
</html>
