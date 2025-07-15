<?php
// admin_login.php - Admin login page
require_once 'config.php';

$error = '';
$success = '';

// Redirect if already logged in
if (isAdmin()) {
    header("Location: admin_dashboard.php");
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Check admin credentials
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            // Login successful
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['login_time'] = time();
            
            // Log the login activity
            logActivity($pdo, null, $admin['id'], 'Admin Login', 'Admin logged in successfully');
            
            // Redirect to dashboard
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'admin_dashboard.php';
            header("Location: " . $redirect);
            exit;
        } else {
            $error = 'Invalid username or password';
            // Log failed login attempt
            error_log("Failed admin login attempt for username: $username from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Case Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 450px;
            width: 100%;
            border: 1px solid rgba(255,255,255,0.2);
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2c3e50, #34495e, #2c3e50);
            background-size: 200% 100%;
            animation: gradientShift 3s ease-in-out infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .admin-logo {
            font-size: 4em;
            color: #2c3e50;
            margin-bottom: 20px;
            position: relative;
        }
        
        .admin-logo::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #2c3e50, #34495e);
            border-radius: 2px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 32px;
            font-weight: 300;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 40px;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .input-container {
            position: relative;
        }
        
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.9);
        }
        
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #2c3e50;
            box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
            transform: translateY(-2px);
        }
        
        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #2c3e50;
            font-size: 18px;
        }
        
        .password-toggle {
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .password-toggle:hover {
            color: #34495e;
        }
        
        .btn {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(44, 62, 80, 0.3);
        }
        
        .btn:active {
            transform: translateY(-1px);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
            font-size: 14px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .success {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
            font-size: 14px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .back-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .back-link {
            color: #2c3e50;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-link:hover {
            color: #34495e;
            transform: translateX(-5px);
        }
        
        .security-info {
            background: rgba(44, 62, 80, 0.05);
            border: 1px solid rgba(44, 62, 80, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            text-align: left;
        }
        
        .security-info h4 {
            color: #2c3e50;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .security-info ul {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            padding-left: 20px;
        }
        
        .security-info li {
            margin-bottom: 5px;
        }
        
        .loading {
            display: none;
            margin-top: 10px;
        }
        
        .loading.show {
            display: block;
        }
        
        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #2c3e50;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 25px;
                margin: 10px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .admin-logo {
                font-size: 3em;
            }
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            color: #666;
        }
        
        .remember-me input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="admin-logo">
            <i class="fas fa-user-shield"></i>
        </div>
        
        <h1>Admin Portal</h1>
        <p class="subtitle">Secure access to the case management system</p>
        
        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Username
                </label>
                <div class="input-container">
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        placeholder="Enter your admin username"
                        value="<?php echo isset($_POST['username']) ? sanitizeInput($_POST['username']) : ''; ?>"
                        autocomplete="username"
                    >
                    <i class="fas fa-user input-icon"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="input-container">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder="Enter your password"
                        autocomplete="current-password"
                    >
                    <i class="fas fa-eye password-toggle input-icon" onclick="togglePassword()"></i>
                </div>
            </div>
            
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember" style="margin: 0; text-transform: none; letter-spacing: normal;">Remember me for 30 days</label>
            </div>
            
            <button type="submit" class="btn" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Login to Admin Panel
            </button>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Authenticating...</p>
            </div>
        </form>
        
        <div class="security-info">
            <h4>
                <i class="fas fa-shield-alt"></i>
                Security Notice
            </h4>
            <ul>
                <li>All login attempts are monitored and logged</li>
                <li>Use strong passwords with at least 8 characters</li>
                <li>Sessions expire after 24 hours of inactivity</li>
                <li>Report any suspicious activity immediately</li>
            </ul>
        </div>
        
        <div class="back-section">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Main Site
            </a>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            const loading = document.getElementById('loading');
            
            btn.disabled = true;
            btn.style.opacity = '0.6';
            loading.classList.add('show');
            
            // If there's an error, re-enable the button after a short delay
            setTimeout(() => {
                if (window.location.href === window.location.href) {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    loading.classList.remove('show');
                }
            }, 3000);
        });
        
        // Auto-focus on username field
        document.getElementById('username').focus();
        
        // Enter key support
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const activeElement = document.activeElement;
                if (activeElement.tagName === 'INPUT') {
                    if (activeElement.id === 'username') {
                        document.getElementById('password').focus();
                    } else if (activeElement.id === 'password') {
                        document.getElementById('loginForm').submit();
                    }
                }
            }
        });
        
        // Clear form on page load if there was an error
        window.addEventListener('load', function() {
            <?php if ($error): ?>
                document.getElementById('password').value = '';
                document.getElementById('password').focus();
            <?php endif; ?>
        });
        
        // Add security warning for dev tools
        console.warn('🚨 SECURITY WARNING: This is a restricted admin area. Unauthorized access is prohibited and monitored.');
        console.warn('⚠️  If you are not an authorized administrator, please close this page immediately.');
    </script>
</body>
</html>