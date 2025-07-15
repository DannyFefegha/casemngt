<?php
// case_details.php - Show case details and activation options
require_once 'config.php';

$error = '';
$success = '';

// Get case ID from URL or POST
$case_id = isset($_GET['case_id']) ? sanitizeInput($_GET['case_id']) : '';
if (empty($case_id) && isset($_POST['case_id'])) {
    $case_id = sanitizeInput($_POST['case_id']);
}

if (empty($case_id)) {
    header("Location: index.php");
    exit;
}

// Fetch case details
$stmt = $pdo->prepare("SELECT * FROM cases WHERE case_id = ?");
$stmt->execute([$case_id]);
$case = $stmt->fetch();

if (!$case) {
    $error = 'Case ID not found!';
}

// Handle activation/decline actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $case) {
    $action = sanitizeInput($_POST['action']);
    
    if ($action === 'activate') {
        $stmt = $pdo->prepare("UPDATE cases SET status = 'activated', updated_at = NOW() WHERE case_id = ?");
        if ($stmt->execute([$case_id])) {
            logActivity($pdo, $case_id, null, 'Case Activated', 'Case activated by user');
            $success = 'Case has been activated successfully!';
            // Redirect to portfolio after 2 seconds
            header("refresh:2;url=portfolio.php?case_id=" . urlencode($case_id));
        } else {
            $error = 'Failed to activate case. Please try again.';
        }
    } elseif ($action === 'decline') {
        $stmt = $pdo->prepare("UPDATE cases SET status = 'declined', updated_at = NOW() WHERE case_id = ?");
        if ($stmt->execute([$case_id])) {
            logActivity($pdo, $case_id, null, 'Case Declined', 'Case declined by user');
            $success = 'Case has been declined.';
            // Redirect to main page after 3 seconds
            header("refresh:3;url=index.php");
        } else {
            $error = 'Failed to decline case. Please try again.';
        }
    }
    
    // Refresh case data
    $stmt = $pdo->prepare("SELECT * FROM cases WHERE case_id = ?");
    $stmt->execute([$case_id]);
    $case = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Case Details - <?php echo $case ? sanitizeInput($case['case_id']) : 'Not Found'; ?></title>
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
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            width: 100%;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .case-header {
            margin-bottom: 30px;
        }
        
        .case-header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 300;
        }
        
        .case-id {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 1px;
        }
        
        .case-info {
            background: rgba(102, 126, 234, 0.05);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: left;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 5px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-value {
            color: #333;
            font-size: 16px;
            line-height: 1.4;
        }
        
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
        }
        
        .status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
        }
        
        .status.pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status.activated {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status.declined {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .action-section {
            margin: 30px 0;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0 10px;
            text-decoration: none;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn.decline {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        
        .btn.decline:hover {
            box-shadow: 0 10px 25px rgba(231, 76, 60, 0.3);
        }
        
        .btn.view {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        }
        
        .btn.view:hover {
            box-shadow: 0 10px 25px rgba(39, 174, 96, 0.3);
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
        }
        
        .back-link {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-link a:hover {
            color: #764ba2;
            transform: translateX(-5px);
        }
        
        .loading {
            display: none;
            margin-top: 20px;
        }
        
        .loading.show {
            display: block;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 25px;
            }
            
            .btn {
                display: block;
                margin: 10px 0;
                width: 100%;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($case): ?>
            <div class="case-header">
                <h1>Case Details</h1>
                <div class="case-id"><?php echo sanitizeInput($case['case_id']); ?></div>
            </div>
            
            <?php if ($error): ?>
                <div class="error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <div class="loading show">
                        <div class="spinner"></div>
                        <p>Redirecting...</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="case-info">
                <div class="info-grid">
                    <div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-user"></i> Full Name
                            </div>
                            <div class="info-value"><?php echo sanitizeInput($case['full_name']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-envelope"></i> Contact Info
                            </div>
                            <div class="info-value"><?php echo nl2br(sanitizeInput($case['contact_info'])); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-exclamation-triangle"></i> Spam Type
                            </div>
                            <div class="info-value"><?php echo sanitizeInput($case['spam_type']); ?></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-building"></i> Broker Name
                            </div>
                            <div class="info-value"><?php echo sanitizeInput($case['broker_name']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-dollar-sign"></i> Recovered Amount
                            </div>
                            <div class="info-value amount">$<?php echo number_format($case['recovered_amount'], 2); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-info-circle"></i> Status
                            </div>
                            <div class="info-value">
                                <span class="status <?php echo $case['status']; ?>">
                                    <?php echo ucfirst($case['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="action-section">
                <?php if ($case['status'] === 'pending'): ?>
                    <form method="POST" style="display: inline;" onsubmit="return confirmAction(this)">
                        <input type="hidden" name="case_id" value="<?php echo sanitizeInput($case['case_id']); ?>">
                        <button type="submit" name="action" value="activate" class="btn">
                            <i class="fas fa-check"></i> Activate Case
                        </button>
                        <button type="submit" name="action" value="decline" class="btn decline">
                            <i class="fas fa-times"></i> Decline Case
                        </button>
                    </form>
                    
                <?php elseif ($case['status'] === 'activated'): ?>
                    <a href="portfolio.php?case_id=<?php echo urlencode($case['case_id']); ?>" class="btn view">
                        <i class="fas fa-folder-open"></i> View Portfolio
                    </a>
                    
                <?php else: ?>
                    <div style="margin: 20px 0;">
                        <i class="fas fa-times-circle" style="color: #e74c3c; font-size: 48px; margin-bottom: 15px;"></i>
                        <h3 style="color: #e74c3c; margin-bottom: 10px;">Case Declined</h3>
                        <p style="color: #666;">This case has been declined and cannot be processed further.</p>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php else: ?>
            <div class="case-header">
                <h1>Case Not Found</h1>
            </div>
            
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
            
            <div style="margin: 30px 0;">
                <i class="fas fa-search" style="color: #667eea; font-size: 48px; margin-bottom: 15px;"></i>
                <p style="color: #666; margin-bottom: 20px;">The case ID you entered could not be found in our system.</p>
                <p style="color: #666; font-size: 14px;">Please check the case ID and try again, or contact support for assistance.</p>
            </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="index.php">
                <i class="fas fa-arrow-left"></i>
                Back to Search
            </a>
        </div>
    </div>
    
    <script>
        function confirmAction(form) {
            const action = form.querySelector('button[type="submit"]:focus').value;
            const actionText = action === 'activate' ? 'activate' : 'decline';
            
            return confirm(`Are you sure you want to ${actionText} this case? This action cannot be undone.`);
        }
        
        // Auto-submit on button click to capture which button was pressed
        document.querySelectorAll('button[name="action"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const action = this.value;
                const actionText = action === 'activate' ? 'activate' : 'decline';
                
                if (confirm(`Are you sure you want to ${actionText} this case?`)) {
                    // Create a hidden input to specify which action was taken
                    const form = this.closest('form');
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = action;
                    form.appendChild(actionInput);
                    
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>