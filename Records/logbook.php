<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

include "connect.php";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get real statistics
$total_docs = 0;
$pending_docs = 0;
$released_docs = 0;
$total_users = 0;

// Get user role and ID from session
$user_role = $_SESSION['role'] ?? 'user';
$user_id = $_SESSION['user_id'] ?? 0;

// Get total documents
if ($user_role === 'admin') {
    $result = $conn->query("SELECT COUNT(*) as count FROM records_document_main");
} else {
    $result = $conn->query("SELECT COUNT(*) as count FROM records_document_main WHERE added_by = $user_id");
}
if ($result) {
    $row = $result->fetch_assoc();
    $total_docs = $row['count'];
}

// Get pending documents (status != 'released')
if ($user_role === 'admin') {
    $result = $conn->query("SELECT COUNT(*) as count FROM records_document_main WHERE document_status != 'released'");
} else {
    $result = $conn->query("SELECT COUNT(*) as count FROM records_document_main WHERE document_status != 'released' AND added_by = $user_id");
}
if ($result) {
    $row = $result->fetch_assoc();
    $pending_docs = $row['count'];
}

// Get released documents
if ($user_role === 'admin') {
    $result = $conn->query("SELECT COUNT(*) as count FROM records_document_main WHERE document_status = 'released'");
} else {
    $result = $conn->query("SELECT COUNT(*) as count FROM records_document_main WHERE document_status = 'released' AND added_by = $user_id");
}
if ($result) {
    $row = $result->fetch_assoc();
    $released_docs = $row['count'];
}

// Get total users (only admins can see this)
if ($user_role === 'admin') {
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        $total_users = $row['count'];
    }
} else {
    // For regular users, show their own documents count instead of total users
    $total_users = $total_docs; // Show their own document count
}

$conn->close();
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PSAU Records System</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="assets/css/psau-style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, var(--psau-gray-50) 0%, var(--psau-lighter) 100%);
            font-family: var(--font-sans);
            min-height: 100vh;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--space-6);
        }
        
        .welcome-section {
            background: linear-gradient(135deg, var(--psau-primary) 0%, var(--psau-secondary) 100%);
            color: var(--psau-white);
            border-radius: var(--radius-2xl);
            padding: var(--space-8);
            margin-bottom: var(--space-8);
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.15"/><circle cx="20" cy="60" r="0.5" fill="white" opacity="0.15"/><circle cx="80" cy="40" r="0.5" fill="white" opacity="0.15"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }
        
        .welcome-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--space-4);
            position: relative;
            z-index: 1;
        }
        
        .welcome-text h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--psau-white);
            margin-bottom: var(--space-2);
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .welcome-text p {
            font-size: 1.25rem;
            opacity: 0.95;
            margin: 0;
            font-weight: 400;
        }
        
        .welcome-logo {
            width: 100px;
            height: 100px;
            background: var(--psau-white);
            border-radius: 50%;
            padding: var(--space-3);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-xl);
        }
        
        .welcome-logo img {
            width: 100%;
            height: auto;
            border-radius: 50%;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: var(--space-6);
            margin-bottom: var(--space-8);
        }
        
        .stat-card {
            background: var(--psau-white);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--psau-gray-200);
            transition: all var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--psau-primary) 0%, var(--psau-secondary) 100%);
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }
        
        .stat-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--psau-light) 0%, var(--psau-lighter) 100%);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: var(--space-4);
            box-shadow: var(--shadow);
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--psau-primary);
            margin-bottom: var(--space-2);
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--psau-gray-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }
        
        /* Graphs Section */
        .graphs-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-6);
            margin-bottom: var(--space-8);
        }
        
        .graph-card {
            background: var(--psau-white);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--psau-gray-200);
            transition: all var(--transition);
        }
        
        .graph-card:hover {
            box-shadow: var(--shadow-xl);
        }
        
        .graph-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-4);
        }
        
        .graph-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--psau-gray-900);
            margin: 0;
        }
        
        .graph-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--psau-light) 0%, var(--psau-lighter) 100%);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            box-shadow: var(--shadow);
        }
        
        .chart-container {
            position: relative;
            height: 250px;
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-6);
            margin-bottom: var(--space-8);
        }
        
        .content-card {
            background: var(--psau-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--psau-gray-200);
            overflow: hidden;
            transition: all var(--transition);
        }
        
        .content-card:hover {
            box-shadow: var(--shadow-xl);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--psau-gray-50) 0%, var(--psau-gray-100) 100%);
            padding: var(--space-4) var(--space-6);
            border-bottom: 1px solid var(--psau-gray-200);
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--psau-gray-900);
            margin: 0;
        }
        
        .card-body {
            padding: var(--space-6);
        }
        
        .quick-actions {
            display: grid;
            gap: var(--space-3);
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            padding: var(--space-4);
            background: var(--psau-white);
            border: 2px solid var(--psau-gray-200);
            border-radius: var(--radius-lg);
            text-decoration: none;
            color: var(--psau-gray-700);
            transition: all var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .action-btn::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 0;
            background: linear-gradient(90deg, var(--psau-primary), var(--psau-secondary));
            transition: width var(--transition);
        }
        
        .action-btn:hover {
            background: linear-gradient(135deg, var(--psau-primary) 0%, var(--psau-secondary) 100%);
            color: var(--psau-white);
            border-color: var(--psau-primary);
            transform: translateX(8px);
            box-shadow: var(--shadow-md);
        }
        
        .action-btn:hover::before {
            width: 4px;
        }
        
        .action-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--psau-gray-100) 0%, var(--psau-gray-200) 100%);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: var(--space-4);
            font-size: 1.5rem;
            transition: all var(--transition);
        }
        
        .action-btn:hover .action-icon {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .action-content h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0 0 var(--space-1) 0;
        }
        
        .action-content p {
            font-size: 0.875rem;
            margin: 0;
            opacity: 0.8;
        }
        
        .recent-activity {
            max-height: 360px;
            overflow-y: auto;
            padding-right: var(--space-2);
        }
        
        .recent-activity::-webkit-scrollbar {
            width: 6px;
        }
        
        .recent-activity::-webkit-scrollbar-track {
            background: var(--psau-gray-100);
            border-radius: var(--radius);
        }
        
        .recent-activity::-webkit-scrollbar-thumb {
            background: var(--psau-gray-300);
            border-radius: var(--radius);
        }
        
        .recent-activity::-webkit-scrollbar-thumb:hover {
            background: var(--psau-gray-400);
        }
        
        .activity-item {
            display: flex;
            align-items: start;
            padding: var(--space-4) 0;
            border-bottom: 1px solid var(--psau-gray-200);
            transition: all var(--transition);
        }
        
        .activity-item:hover {
            background: var(--psau-gray-50);
            margin: 0 calc(-1 * var(--space-6));
            padding-left: var(--space-6);
            padding-right: var(--space-6);
            border-radius: var(--radius-lg);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--psau-light) 0%, var(--psau-lighter) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: var(--space-4);
            font-size: 1rem;
            flex-shrink: 0;
            box-shadow: var(--shadow);
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 600;
            color: var(--psau-gray-900);
            margin-bottom: var(--space-1);
            font-size: 0.9375rem;
        }
        
        .activity-time {
            font-size: 0.75rem;
            color: var(--psau-gray-500);
            font-weight: 500;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--psau-gray-600);
            text-decoration: none;
            margin-bottom: var(--space-6);
            font-size: 0.875rem;
            font-weight: 500;
            padding: var(--space-2) var(--space-3);
            border-radius: var(--radius);
            transition: all var(--transition);
        }
        
        .back-link:hover {
            color: var(--psau-primary);
            background: var(--psau-light);
            transform: translateX(-4px);
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: var(--space-4);
            }
            
            .welcome-content {
                flex-direction: column;
                text-align: center;
            }
            
            .welcome-text h1 {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .graphs-section {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .welcome-section {
                padding: var(--space-6);
            }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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
    <div class="dashboard-container fade-in">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-content">
                <div class="welcome-text">
                    <h1>Welcome to PSAU Records</h1>
                    <p>Document Tracking System Dashboard</p>
                </div>
                <div class="welcome-logo pulse">
                    <img src="PSAU_10.jpg" alt="PSAU Logo">
                </div>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📄</div>
                <div class="stat-value"><?php echo number_format($total_docs); ?></div>
                <div class="stat-label">Total Documents</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-value"><?php echo number_format($pending_docs); ?></div>
                <div class="stat-label">For Releasing</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?php echo number_format($released_docs); ?></div>
                <div class="stat-label">Released</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-value"><?php echo number_format($total_users); ?></div>
                <div class="stat-label"><?php echo ($user_role === 'admin') ? 'System Users' : 'Your Documents'; ?></div>
            </div>
        </div>
        
        <!-- Graphs Section -->
        <div class="graphs-section">
            <div class="graph-card">
                <div class="graph-header">
                    <h3 class="graph-title">Document Status Overview</h3>
                    <div class="graph-icon">📊</div>
                </div>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <div class="graph-card">
                <div class="graph-header">
                    <h3 class="graph-title">Document Distribution</h3>
                    <div class="graph-icon">📈</div>
                </div>
                <div class="chart-container">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Statistics are now loaded server-side
            
            // Chart configurations
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            };
            
            // Document Status Overview (Pie Chart)
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: ['For Releasing', 'Released'],
                    datasets: [{
                        data: [<?php echo $pending_docs; ?>, <?php echo $released_docs; ?>],
                        backgroundColor: [
                            'rgba(74, 157, 106, 0.8)',
                            'rgba(34, 197, 94, 0.8)'
                        ],
                        borderColor: [
                            'rgba(74, 157, 106, 1)',
                            'rgba(34, 197, 94, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
            
            // Document Distribution (Bar Chart)
            const distributionCtx = document.getElementById('distributionChart').getContext('2d');
            new Chart(distributionCtx, {
                type: 'bar',
                data: {
                    labels: ['Total Documents', 'For Releasing', 'Released', '<?php echo ($user_role === 'admin') ? 'System Users' : 'Your Documents'; ?>'],
                    datasets: [{
                        label: 'Count',
                        data: [<?php echo $total_docs; ?>, <?php echo $pending_docs; ?>, <?php echo $released_docs; ?>, <?php echo $total_users; ?>],
                        backgroundColor: [
                            'rgba(74, 157, 106, 0.8)',
                            'rgba(251, 146, 60, 0.8)',
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(59, 130, 246, 0.8)'
                        ],
                        borderColor: [
                            'rgba(74, 157, 106, 1)',
                            'rgba(251, 146, 60, 1)',
                            'rgba(34, 197, 94, 1)',
                            'rgba(59, 130, 246, 1)'
                        ],
                        borderWidth: 2,
                        borderRadius: 8
                    }]
                },
                options: {
                    ...chartOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        ...chartOptions.plugins,
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
