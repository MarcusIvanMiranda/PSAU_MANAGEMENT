<?php include "connect.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);
$datatable = "property_list"; // MySQL table name
$results_per_page = 27; // number of results per page
 
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
    <title>Property Management System - PSAU</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
          /* PSAU Brand Colors */
          --psau-primary: #1e5a3d;
          --psau-secondary: #2d7a4f;
          --psau-accent: #4a9d6a;
          --psau-light: #e8f5ee;
          --psau-lighter: #f0faf6;
          
          /* Dashboard Colors */
          --psau-green: #4CAF50;
          --psau-dark-green: #388E3C;
          --psau-light-green: #8BC34A;
          --psau-white: #FFFFFF;
          --psau-light-gray: #F5F5F5;
          --psau-gray: #E0E0E0;
          --psau-dark-gray: #757575;
          --psau-text-color: #333333;
          --psau-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
          --psau-border-radius: 8px;
          
          /* Neutral Colors */
          --psau-gray-50: #f9fafb;
          --psau-gray-100: #f3f4f6;
          --psau-gray-200: #e5e7eb;
          --psau-gray-300: #d1d5db;
          --psau-gray-400: #9ca3af;
          --psau-gray-500: #6b7280;
          --psau-gray-600: #4b5563;
          --psau-gray-700: #374151;
          --psau-gray-800: #1f2937;
          --psau-gray-900: #111827;
          
          /* Status Colors */
          --psau-success: #059669;
          --psau-warning: #d97706;
          --psau-error: #dc2626;
          --psau-info: #2563eb;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: var(--psau-light-gray);
            color: var(--psau-text-color);
            padding: 20px;
        }
        
        .container {
            max-width: 95%;
            width: 100%;
            margin: 0 auto;
            background: var(--psau-white);
            border-radius: var(--psau-border-radius);
            box-shadow: var(--psau-shadow);
            overflow: hidden;
        }
        
        .header {
            background: var(--psau-primary);
            color: var(--psau-white);
            padding: 30px;
            text-align: center;
            border-radius: var(--psau-border-radius) var(--psau-border-radius) 0 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        
        .header-logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: var(--psau-shadow);
        }
        
        .header-content {
            flex: 1;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .search-section {
            padding: 30px;
            background: var(--psau-white);
            border-bottom: 1px solid var(--psau-gray);
        }
        
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 0;
            justify-content: center;
        }
        
        .search-input {
            flex-grow: 1;
            max-width: 400px;
            padding: 12px 15px;
            border: 1px solid var(--psau-gray);
            border-radius: var(--psau-border-radius);
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--psau-primary);
            box-shadow: 0 0 0 2px rgba(30, 90, 61, 0.2);
        }
        
        .search-btn {
            background: var(--psau-primary);
            color: var(--psau-white);
            border: none;
            padding: 12px 20px;
            border-radius: var(--psau-border-radius);
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .search-btn:hover {
            background: var(--psau-secondary);
            transform: translateY(-1px);
            box-shadow: var(--psau-shadow);
        }
        
        .table-container {
            padding: 30px;
            background: var(--psau-white);
        }
        
        .modern-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--psau-white);
            border-radius: var(--psau-border-radius);
            overflow: hidden;
            box-shadow: var(--psau-shadow);
            table-layout: auto;
        }
        
        .modern-table thead {
            background: var(--psau-primary);
            color: var(--psau-white);
        }
        
        .modern-table th {
            padding: 15px 12px;
            text-align: center;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        .modern-table td {
            padding: 12px 8px;
            text-align: center;
            border-bottom: 1px solid var(--psau-gray);
            font-size: 12px;
            vertical-align: middle;
            white-space: normal;
            word-wrap: break-word;
        }
        
        .modern-table tbody tr {
            transition: all 0.2s ease;
        }
        
        .modern-table tbody tr:hover {
            background: var(--psau-light-gray);
        }
        
        .modern-table tbody tr:nth-child(even) {
            background: #fafafa;
        }
        
        .pagination {
            padding: 30px;
            text-align: center;
            background: var(--psau-white);
            border-top: 1px solid var(--psau-gray);
            border-radius: 0 0 var(--psau-border-radius) var(--psau-border-radius);
        }
        
        .pagination a {
            display: inline-block;
            padding: 10px 15px;
            margin: 0 3px;
            text-decoration: none;
            border: 1px solid var(--psau-gray);
            border-radius: var(--psau-border-radius);
            color: var(--psau-text-color);
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .pagination a:hover {
            background: var(--psau-primary);
            color: var(--psau-white);
            border-color: var(--psau-primary);
            transform: translateY(-1px);
            box-shadow: var(--psau-shadow);
        }
        
        .curPage {
            background: var(--psau-primary) !important;
            color: var(--psau-white) !important;
            border-color: var(--psau-primary) !important;
        }
        
        .prev-next {
            background: var(--psau-accent) !important;
            color: var(--psau-white) !important;
            border-color: var(--psau-accent) !important;
            font-weight: 600;
        }
        
        .prev-next:hover {
            background: var(--psau-primary) !important;
            transform: translateY(-1px);
            box-shadow: var(--psau-shadow);
        }
        
        .no-results {
            text-align: center;
            padding: 50px;
            color: #6c757d;
            font-size: 18px;
        }
        
        @media (max-width: 1200px) {
            .modern-table th {
                font-size: 10px;
                padding: 8px 4px;
            }
            .modern-table td {
                font-size: 10px;
                padding: 6px 4px;
            }
        }
        
        @media (max-width: 768px) {
            .search-input {
                width: 100%;
            }
            
            .modern-table {
                font-size: 9px;
            }
            
            .modern-table th,
            .modern-table td {
                padding: 6px 2px;
                font-size: 9px;
            }
            
            .header h1 {
                font-size: 1.8em;
            }
            
            .table-container {
                padding: 15px;
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
    <div class="container">
        <div class="header">
            <img src="PSAU_10.jpg" alt="PSAU Logo" class="header-logo">
            <div class="header-content">
                <h1>Property Management System</h1>
                <p>Pampanga State Agricultural University</p>
            </div>
        </div>
        
        <div class="search-section">
            <form action='index.php' method='GET' class="search-form">
                <input id='filtertext' name='filtertext' type='text' class="search-input" 
                       placeholder='🔍 Search Property No / Tag / Item / Description' 
                       value='<?php echo htmlspecialchars($filtertext); ?>'>
                <button type='submit' class="search-btn">Search</button>
            </form>
        </div>

<?php
$filtertext = isset($_GET['filtertext']) ? $_GET['filtertext'] : '';
$filtertext = trim($filtertext);
$delivered='FOR RELEASING';
if (isset($_GET["page"])) { $page = (int)$_GET["page"]; } else { $page=1; };
$start_from = ($page-1) * $results_per_page;

// Build WHERE condition
$where_condition = "";
if (!empty($filtertext)) {
    $where_condition = " where (property_no like '%".$filtertext."%' or property_tag like '%".$filtertext."%' or property_item like '%".$filtertext."%' or property_description like '%".$filtertext."%')";
}

$sql = "SELECT * FROM ".$datatable.$where_condition." order by property_no desc LIMIT $start_from, ".$results_per_page;
$rs_result = $conn->query($sql);
// Debug: Show SQL and result count
echo "<!-- SQL: " . $sql . " -->";
echo "<!-- Results found: " . $rs_result->num_rows . " -->";

$sql = "SELECT COUNT(*) AS total FROM ".$datatable.$where_condition;
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_pages = ceil($row["total"] / $results_per_page); // calculate total pages with results
?>
        <div class="table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>PROPERTY NO.</th>
                        <th>PROPERTY TAG</th>
                        <th>PROPERTY ITEM</th>
                        <th>PROPERTY DESCRIPTION</th>
                        <th>MODEL NUMBER</th>
                        <th>SERIAL NUMBER</th>
                        <th>PROPERTY VALUE</th>
                        <th>ACQUISITION DATE</th>
                        <th>ACCOUNTABLE PERSON</th>
                        <th>ACTUAL LOCATION</th>
                        <th>REMARKS</th>
                        <th>COUNTED</th>
                        <th>CONDITION</th>
                        <th>VALIDATED</th>
                        <th>STATUS</th>
                        <th>FUND</th>
                        <th>YEAR PURCHASED</th>
                        <th>SM GROUP ACCOUNT</th>
                        <th>GL ACCOUNT</th>
                        <th>PROPERTY NUMBER</th>
                        <th>PROPERTY LOC</th>
                    </tr>
                </thead>
                <tbody>
<?php
if ($rs_result->num_rows == 0) {
    echo "<tr><td colspan='23' class='no-results'>No properties found matching '" . htmlspecialchars($filtertext) . "'</td></tr>";
} else {
    while($row = $rs_result->fetch_assoc()) {
?>
                    <tr>
                        <td><?php echo $row["idproperty_list"]; ?></td>
                        <td><?php echo $row["property_no"]; ?></td>
                        <td><?php echo $row["property_tag"]; ?></td>
                        <td><?php echo $row["property_item"]; ?></td>
                        <td><?php echo $row["property_description"]; ?></td>
                        <td><?php echo $row["property_model_number"]; ?></td>
                        <td><?php echo $row["property_serial_number"]; ?></td>
                        <td><?php echo $row["property_value"]; ?></td>
                        <td><?php echo $row["property_acquisition_date"]; ?></td>
                        <td><?php echo $row["property_accountable_person"]; ?></td>
                        <td><?php echo $row["property_actual_location"]; ?></td>
                        <td><?php echo $row["property_remarks"]; ?></td>
                        <td><?php echo $row["property_counted"]; ?></td>
                        <td><?php echo $row["property_condition"]; ?></td>
                        <td><?php echo $row["property_validated"]; ?></td>
                        <td><?php echo $row["property_status"]; ?></td>
                        <td><?php echo $row["property_fund"]; ?></td>
                        <td><?php echo $row["property_year_purchased"]; ?></td>
                        <td><?php echo $row["property_sm_group_account"]; ?></td>
                        <td><?php echo $row["property_gl_account"]; ?></td>
                        <td><?php echo $row["property_number"]; ?></td>
                        <td><?php echo $row["property_loc"]; ?></td>
                    </tr>
<?php
    } // end while
} // end if else
?>
                </tbody>
            </table>
        </div>
        
        <div class="pagination">
<?php
// Previous button
if ($page > 1) {
    echo "<a href='index.php?filtertext=".$filtertext."&page=".($page-1)."' class='prev-next'><i class='fas fa-chevron-left'></i> Previous</a>";
}

// Page numbers - show only 10 numbers at a time
$max_visible_pages = 10;
$half_visible = floor($max_visible_pages / 2);

$start_page = max(1, $page - $half_visible);
$end_page = min($total_pages, $start_page + $max_visible_pages - 1);

// Adjust start_page if we're near the end
if ($end_page - $start_page < $max_visible_pages - 1) {
    $start_page = max(1, $end_page - $max_visible_pages + 1);
}

for ($i=$start_page; $i<=$end_page; $i++) {
    echo "<a href='index.php?filtertext=".htmlspecialchars($filtertext)."&page=".$i."'";
    if ($i==$page) {
        echo " class='curPage'";
    }
    echo ">".$i."</a> ";
}

// Show ellipsis and last page if needed
if ($end_page < $total_pages) {
    if ($end_page < $total_pages - 1) {
        echo "<span style='margin: 0 10px; color: var(--psau-gray-500);'>...</span>";
    }
    echo "<a href='index.php?filtertext=".htmlspecialchars($filtertext)."&page=".$total_pages."'";
    if ($total_pages==$page) {
        echo " class='curPage'";
    }
    echo ">".$total_pages."</a> ";
}

// Next button
if ($page < $total_pages) {
    echo "<a href='index.php?filtertext=".$filtertext."&page=".($page+1)."' class='prev-next'>Next <i class='fas fa-chevron-right'></i></a>";
}
?>
        </div>
    </div>
</body>
</html>
