<?php
// index.php - Main landing page for case ID search
require_once 'config.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['case_id'])) {
    $case_id = sanitizeInput($_POST['case_id']);
    
    if (empty($case_id)) {
        $error = 'Please enter a case ID';
    } else {
        // Check if case exists
        $stmt = $pdo->prepare("SELECT id FROM cases WHERE case_id = ?");
        $stmt->execute([$case_id]);
        
        if ($stmt->fetch()) {
            header("Location: case_details.php?case_id=" . urlencode($case_id));
            exit;
        } else {
            $error = 'Case ID not found. Please check and try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Case Management System - Find Case</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 450px;
            width: 100%;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .logo {
            font-size: 3em;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 15px;
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
            margin-bottom: 10px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .input-container {
            position: relative;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.9);
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
        
        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 18px;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn:active {
            transform: translateY(-1px);
        }
        
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
            font-size: 14px;
        }
        
        .success {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
            font-size: 14px;
        }
        
        .admin-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .admin-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .admin-link:hover {
            color: #764ba2;
            transform: translateX(5px);
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .feature {
            text-align: center;
            padding: 15px 10px;
            border-radius: 10px;
            background: rgba(102, 126, 234, 0.05);
        }
        
        .feature-icon {
            font-size: 24px;
            color: #667eea;
            margin-bottom: 8px;
        }
        
        .feature-text {
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 30px 25px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .features {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <i class="fas fa-search-dollar"></i>
        </div>
        
        <h1>Find Your Case</h1>
        <p class="subtitle">Enter your case ID to access your portfolio and case details</p>
        
        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="case_id">
                    <i class="fas fa-file-alt"></i> Case ID
                </label>
                <div class="input-container">
                    <input 
                        type="text" 
                        id="case_id" 
                        name="case_id" 
                        required 
                        placeholder="Enter your case ID (e.g., CASE12345)"
                        value="<?php echo isset($_POST['case_id']) ? sanitizeInput($_POST['case_id']) : ''; ?>"
                        autocomplete="off"
                    >
                    <i class="fas fa-search input-icon"></i>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-search"></i> Search Case
            </button>
        </form>
        
        <div class="features">
            <div class="feature">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="feature-text">Secure Access</div>
            </div>
            <div class="feature">
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <div class="feature-text">Portfolio View</div>
            </div>
            <div class="feature">
                <div class="feature-icon"><i class="fas fa-users"></i></div>
                <div class="feature-text">Agent Support</div>
            </div>
            <div class="feature">
                <div class="feature-icon"><i class="fas fa-credit-card"></i></div>
                <div class="feature-text">Payment Options</div>
            </div>
        </div>
        
        <div class="admin-section">
            <a href="admin_login.php" class="admin-link">
                <i class="fas fa-user-shield"></i>
                Administrator Access
            </a>
        </div>
    </div>
    
    <script>
        // Auto-format case ID input
        document.getElementById('case_id').addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase();
            // Remove any non-alphanumeric characters except spaces and dashes
            value = value.replace(/[^A-Z0-9\s-]/g, '');
            e.target.value = value;
        });
        
        // Add enter key support
        document.getElementById('case_id').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html>
