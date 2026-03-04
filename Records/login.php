<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PSAU Records System</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="assets/css/psau-style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: var(--space-4);
            background: linear-gradient(135deg, var(--psau-light) 0%, var(--psau-lighter) 100%);
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        
        .login-card {
            background: var(--psau-white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            border: 1px solid var(--psau-gray-200);
            backdrop-filter: blur(10px);
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--psau-primary) 0%, var(--psau-secondary) 100%);
            color: var(--psau-white);
            text-align: center;
            padding: var(--space-8) var(--space-6);
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.15"/><circle cx="20" cy="60" r="0.5" fill="white" opacity="0.15"/><circle cx="80" cy="40" r="0.5" fill="white" opacity="0.15"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }
        
        .login-logo {
            width: 72px;
            height: 72px;
            margin: 0 auto var(--space-4);
            background: var(--psau-white);
            border-radius: 50%;
            padding: var(--space-2);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-lg);
            position: relative;
            z-index: 1;
        }
        
        .login-logo img {
            width: 100%;
            height: auto;
            border-radius: 50%;
        }
        
        .login-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: var(--space-2);
            position: relative;
            z-index: 1;
        }
        
        .login-subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 0;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }
        
        .login-body {
            padding: var(--space-8) var(--space-6);
        }
        
        .form-group {
            margin-bottom: var(--space-6);
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: var(--space-2);
            color: var(--psau-gray-700);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }
        
        .form-control {
            padding: var(--space-4) var(--space-4);
            border: 2px solid var(--psau-gray-200);
            border-radius: var(--radius-lg);
            font-size: 0.9375rem;
            transition: all var(--transition);
            background: var(--psau-gray-50);
        }
        
        .form-control:focus {
            border-color: var(--psau-primary);
            box-shadow: 0 0 0 3px rgb(30 90 61 / 0.1);
            background: var(--psau-white);
            outline: none;
        }
        
        .btn-login {
            width: 100%;
            padding: var(--space-4) var(--space-6);
            background: linear-gradient(135deg, var(--psau-primary) 0%, var(--psau-secondary) 100%);
            color: var(--psau-white);
            border: none;
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all var(--transition);
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert-error {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: var(--space-4);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-6);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }
        
        .alert-error::before {
            content: '⚠️';
            font-size: 1.125rem;
        }
        
        .login-footer {
            background: linear-gradient(135deg, var(--psau-gray-50) 0%, var(--psau-gray-100) 100%);
            padding: var(--space-6);
            text-align: center;
            border-top: 1px solid var(--psau-gray-200);
        }
        
        .login-footer p {
            margin: 0;
            font-size: 0.75rem;
            color: var(--psau-gray-500);
            line-height: 1.5;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon::before {
            content: '👤';
            position: absolute;
            left: var(--space-4);
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            z-index: 1;
        }
        
        .input-icon.password::before {
            content: '🔒';
        }
        
        .input-icon .form-control {
            padding-left: var(--space-12);
        }
        
        @media (max-width: 480px) {
            .login-card {
                margin: 0;
                border-radius: 0;
                min-height: 100vh;
            }
            
            .login-header {
                padding: var(--space-6) var(--space-4);
            }
            
            .login-body {
                padding: var(--space-6) var(--space-4);
            }
            
            body {
                padding: 0;
            }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container fade-in">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <img src="PSAU_10.jpg" alt="PSAU Logo">
                </div>
                <h1 class="login-title">Welcome Back</h1>
                <p class="login-subtitle">PSAU Records Unit - Document Tracking System</p>
            </div>
            
            <div class="login-body">
                <?php
                if (isset($_GET['error'])) {
                    echo '<div class="alert-error">Invalid username or password</div>';
                }
                ?>
                
                <form action="authenticate.php" method="post" autocomplete="off">
                    <div class="form-group">
                        <label for="username" class="form-label">
                            Username
                        </label>
                        <div class="input-icon">
                            <input type="text" id="username" name="username" class="form-control" required autocomplete="new-username" readonly onfocus="this.removeAttribute('readonly')" placeholder="Enter your username">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">
                            Password
                        </label>
                        <div class="input-icon password">
                            <input type="password" id="password" name="password" class="form-control" required autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly')" placeholder="Enter your password">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        Sign In
                    </button>
                </form>
            </div>
            
            <div class="login-footer">
                <p>&copy; <?php echo date('Y'); ?> Pampanga State Agricultural University<br>Records Unit Document Tracking System</p>
            </div>
        </div>
    </div>
</body>
</html>
