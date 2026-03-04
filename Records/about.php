<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - PSAU Records System</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="assets/css/psau-style.css">
    <style>
        :root {
            --psau-primary: #1e5a3d;
            --psau-secondary: #2d7a4f;
            --psau-accent: #4a9d6a;
            --psau-light: #e8f5ee;
            --psau-lighter: #f0faf6;
            --psau-white: #FFFFFF;
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
            --space-1: 0.25rem;
            --space-2: 0.5rem;
            --space-3: 0.75rem;
            --space-4: 1rem;
            --space-6: 1.5rem;
            --space-8: 2rem;
            --radius: 0.375rem;
            --radius-lg: 0.5rem;
            --radius-2xl: 1rem;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, var(--psau-light) 0%, var(--psau-lighter) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .about-container {
            max-width: 800px;
            margin: var(--space-6);
            width: 100%;
        }
        
        .about-card {
            background: var(--psau-white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            border: 1px solid var(--psau-gray-200);
            position: relative;
        }
        
        .about-header {
            background: linear-gradient(135deg, var(--psau-primary) 0%, var(--psau-secondary) 100%);
            color: var(--psau-white);
            padding: var(--space-8) var(--space-6);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .about-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.15"/><circle cx="20" cy="60" r="0.5" fill="white" opacity="0.15"/><circle cx="80" cy="40" r="0.5" fill="white" opacity="0.15"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }
        
        .about-logo {
            width: 80px;
            height: 80px;
            background: var(--psau-white);
            border-radius: 50%;
            padding: var(--space-2);
            margin: 0 auto var(--space-4);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-lg);
            position: relative;
            z-index: 1;
        }
        
        .about-logo img {
            width: 100%;
            height: auto;
            border-radius: 50%;
        }
        
        .about-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: var(--space-2);
            position: relative;
            z-index: 1;
            color: var(--psau-white);
        }
        
        .about-subtitle {
            font-size: 1.125rem;
            opacity: 0.9;
            margin: 0;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }
        
        .about-body {
            padding: var(--space-8) var(--space-6);
        }
        
        .info-section {
            margin-bottom: var(--space-6);
        }
        
        .info-section:last-child {
            margin-bottom: 0;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--psau-primary);
            margin-bottom: var(--space-4);
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }
        
        .section-title::before {
            content: '';
            width: 4px;
            height: 24px;
            background: linear-gradient(135deg, var(--psau-primary) 0%, var(--psau-secondary) 100%);
            border-radius: var(--radius);
        }
        
        .info-content {
            font-size: 1rem;
            line-height: 1.7;
            color: var(--psau-gray-700);
            margin: 0;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-6);
            margin-top: var(--space-6);
        }
        
        .info-item {
            padding: var(--space-4);
            background: linear-gradient(135deg, var(--psau-gray-50) 0%, var(--psau-light) 100%);
            border-radius: var(--radius-lg);
            border: 1px solid var(--psau-gray-200);
            transition: all var(--transition);
        }
        
        .info-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .info-label {
            font-weight: 600;
            color: var(--psau-gray-900);
            margin-bottom: var(--space-2);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .info-value {
            color: var(--psau-gray-700);
            font-size: 1rem;
            line-height: 1.6;
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
        
        .footer-info {
            text-align: center;
            padding: var(--space-6);
            background: linear-gradient(135deg, var(--psau-gray-50) 0%, var(--psau-gray-100) 100%);
            border-top: 1px solid var(--psau-gray-200);
        }
        
        .footer-info p {
            margin: 0;
            font-size: 0.875rem;
            color: var(--psau-gray-500);
            line-height: 1.5;
        }
        
        .highlight {
            background: linear-gradient(135deg, var(--psau-light) 0%, var(--psau-lighter) 100%);
            padding: var(--space-1) var(--space-2);
            border-radius: var(--radius);
            font-weight: 600;
            color: var(--psau-primary);
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            .about-container {
                margin: var(--space-4);
            }
            
            .about-header {
                padding: var(--space-6) var(--space-4);
            }
            
            .about-body {
                padding: var(--space-6) var(--space-4);
            }
            
            .about-title {
                font-size: 1.75rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
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
    <div class="about-container fade-in">
        
        <div class="about-card">
            <div class="about-header">
                <div class="about-logo">
                    <img src="PSAU_10.jpg" alt="PSAU Logo">
                </div>
                <h1 class="about-title">Document Tracking System</h1>
                <p class="about-subtitle">Pampanga State Agricultural University</p>
            </div>
            
            <div class="about-body">
                <div class="info-section">
                    <h2 class="section-title">System Overview</h2>
                    <p class="info-content">
                        The PSAU Document Tracking System is a comprehensive digital solution designed to streamline the management, tracking, and monitoring of documents within the Pampanga State Agricultural University. This system ensures efficient document workflow, enhanced security, and improved accountability across all university departments.
                    </p>
                </div>
                
                <div class="info-section">
                    <h2 class="section-title">Development Team</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">University</div>
                            <div class="info-value">Pampanga State Agricultural University</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Development Unit</div>
                            <div class="info-value">Management Information System Unit</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Version</div>
                            <div class="info-value">v1.0.0</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Release Date</div>
                            <div class="info-value">June 10, 2024</div>
                        </div>
                    </div>
                </div>
                
                <div class="info-section">
                    <h2 class="section-title">Key Features</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">📄 Document Management</div>
                            <div class="info-value">Comprehensive document registration, tracking, and archiving capabilities</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">👥 User Management</div>
                            <div class="info-value">Role-based access control with secure authentication system</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">📊 Real-time Monitoring</div>
                            <div class="info-value">Live tracking of document status and workflow progress</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">🔍 Advanced Search</div>
                            <div class="info-value">Quick and efficient document retrieval with smart search filters</div>
                        </div>
                    </div>
                </div>
                
                <div class="info-section">
                    <h2 class="section-title">System Information</h2>
                    <p class="info-content">
                        This Document Tracking System is built with modern web technologies, ensuring <span class="highlight">security</span>, <span class="highlight">scalability</span>, and <span class="highlight">user-friendly experience</span>. The system adheres to PSAU's digital transformation initiatives and complies with university data management policies.
                    </p>
                </div>
            </div>
            
            <div class="footer-info">
                <p>&copy; <?php echo date('Y'); ?> Pampanga State Agricultural University<br>Management Information System Unit • Document Tracking System</p>
            </div>
        </div>
    </div>
</body>
</html>
