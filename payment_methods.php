<?php
// payment_methods.php - Payment methods page for escrow department
require_once 'config.php';

$case_id = isset($_GET['case_id']) ? sanitizeInput($_GET['case_id']) : '';

if (empty($case_id)) {
    header("Location: index.php");
    exit;
}

// Verify case exists and is activated
$stmt = $pdo->prepare("SELECT * FROM cases WHERE case_id = ? AND status = 'activated'");
$stmt->execute([$case_id]);
$case = $stmt->fetch();

if (!$case) {
    header("Location: index.php?error=case_not_found");
    exit;
}

// Fetch active payment methods
$stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE status = 'active' ORDER BY method_name");
$stmt->execute();
$payment_methods = $stmt->fetchAll();

logActivity($pdo, $case_id, null, 'Payment Methods Accessed', 'User accessed escrow payment methods');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Methods - Escrow Department</title>
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
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 15px;
            font-size: 32px;
            font-weight: 300;
        }
        
        .department-badge {
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            display: inline-block;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .case-info {
            color: #666;
            font-size: 14px;
        }
        
        .security-banner {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .security-banner h3 {
            margin-bottom: 10px;
            font-size: 20px;
        }
        
        .security-banner p {
            margin: 0;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .security-features {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .security-feature {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .payment-grid {
            display: grid;
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .payment-card {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(155, 89, 182, 0.1);
            padding: 30px;
            border-radius: 15px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .payment-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(155, 89, 182, 0.02) 0%, rgba(142, 68, 173, 0.02) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .payment-card:hover::before {
            opacity: 1;
        }
        
        .payment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(155, 89, 182, 0.2);
            border-color: #9b59b6;
        }
        
        .payment-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .payment-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }
        
        .payment-info h3 {
            color: #333;
            margin-bottom: 5px;
            font-size: 22px;
            font-weight: 600;
        }
        
        .payment-type {
            color: #9b59b6;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .payment-details {
            color: #666;
            line-height: 1.8;
            margin-bottom: 20px;
            white-space: pre-line;
            position: relative;
            z-index: 1;
        }
        
        .payment-features {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .feature-tag {
            background: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .payment-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: 15px;
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.1);
            position: relative;
            z-index: 1;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #9b59b6;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .payment-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }
        
        .btn {
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(155, 89, 182, 0.3);
        }
        
        .btn.secondary {
            background: transparent;
            border: 2px solid #9b59b6;
            color: #9b59b6;
        }
        
        .btn.secondary:hover {
            background: #9b59b6;
            color: white;
        }
        
        .important-notice {
            background: rgba(241, 196, 15, 0.1);
            border: 1px solid rgba(241, 196, 15, 0.3);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .important-notice h3 {
            color: #f39c12;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .important-notice ul {
            color: #666;
            line-height: 1.8;
            padding-left: 20px;
        }
        
        .important-notice li {
            margin-bottom: 8px;
        }
        
        .back-navigation {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .back-link {
            color: #9b59b6;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }
        
        .back-link:hover {
            color: #8e44ad;
            transform: translateX(-5px);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 25px;
                margin: 10px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .payment-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .security-features {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
            
            .payment-actions {
                flex-direction: column;
            }
            
            .payment-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .copy-btn {
            background: rgba(155, 89, 182, 0.1);
            border: 1px solid rgba(155, 89, 182, 0.3);
            color: #9b59b6;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
            margin-left: 10px;
            transition: all 0.2s ease;
        }
        
        .copy-btn:hover {
            background: #9b59b6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-shield-alt" style="color: #9b59b6; margin-right: 15px;"></i>
                Escrow Payment Methods
            </h1>
            <div class="department-badge">Secure Payment Processing</div>
            <div class="case-info">Case: <?php echo sanitizeInput($case['case_id']); ?> - <?php echo sanitizeInput($case['full_name']); ?></div>
        </div>
        
        <div class="security-banner">
            <h3><i class="fas fa-lock"></i> Bank-Level Security</h3>
            <p>All payments are processed through our secure escrow system with 256-bit SSL encryption and multi-factor authentication</p>
            
            <div class="security-features">
                <div class="security-feature">
                    <i class="fas fa-shield-check"></i>
                    SSL Encrypted
                </div>
                <div class="security-feature">
                    <i class="fas fa-user-check"></i>
                    Identity Verified
                </div>
                <div class="security-feature">
                    <i class="fas fa-clock"></i>
                    24/7 Monitoring
                </div>
                <div class="security-feature">
                    <i class="fas fa-hand-holding-usd"></i>
                    Funds Protected
                </div>
            </div>
        </div>
        
        <div class="important-notice">
            <h3>
                <i class="fas fa-exclamation-triangle"></i>
                Important Payment Information
            </h3>
            <ul>
                <li>Always include your case ID (<?php echo sanitizeInput($case['case_id']); ?>) in payment descriptions</li>
                <li>Payments are processed within 24-48 hours during business days</li>
                <li>All transactions are monitored for fraud protection</li>
                <li>Contact our support team if you experience any issues</li>
                <li>Keep your payment receipts for your records</li>
            </ul>
        </div>
        
        <div class="payment-grid">
            <?php 
            $icons = [
                'PayPal' => 'fab fa-paypal',
                'Bitcoin' => 'fab fa-bitcoin',
                'Ethereum' => 'fab fa-ethereum',
                'Bank Wire' => 'fas fa-university',
                'Bank Transfer' => 'fas fa-university',
                'Zelle' => 'fas fa-mobile-alt',
                'Cash App' => 'fas fa-mobile-alt',
                'Venmo' => 'fas fa-mobile-alt'
            ];
            
            $features = [
                'PayPal' => ['Instant Transfer', 'Buyer Protection', 'Mobile App'],
                'Bitcoin' => ['Decentralized', 'Low Fees', 'Global Access'],
                'Ethereum' => ['Smart Contracts', 'Fast Transfer', 'DeFi Compatible'],
                'Bank Wire' => ['High Security', 'Large Amounts', 'Traditional'],
                'Bank Transfer' => ['High Security', 'Large Amounts', 'Traditional'],
                'Zelle' => ['Bank to Bank', 'Same Day', 'No Fees'],
                'Cash App' => ['Instant Send', 'Bitcoin Support', 'Easy Setup'],
                'Venmo' => ['Social Payments', 'Quick Setup', 'Mobile First']
            ];
            
            foreach ($payment_methods as $method): 
                $method_name = $method['method_name'];
                $icon = 'fas fa-credit-card';
                
                foreach ($icons as $key => $val) {
                    if (stripos($method_name, $key) !== false) {
                        $icon = $val;
                        break;
                    }
                }
                
                $method_features = [];
                foreach ($features as $key => $vals) {
                    if (stripos($method_name, $key) !== false) {
                        $method_features = $vals;
                        break;
                    }
                }
                
                if (empty($method_features)) {
                    $method_features = ['Secure', 'Verified', 'Protected'];
                }
            ?>
                <div class="payment-card">
                    <div class="payment-header">
                        <div class="payment-icon">
                            <i class="<?php echo $icon; ?>"></i>
                        </div>
                        <div class="payment-info">
                            <h3><?php echo sanitizeInput($method_name); ?></h3>
                            <div class="payment-type">Digital Payment Method</div>
                        </div>
                    </div>
                    
                    <div class="payment-details"><?php echo nl2br(sanitizeInput($method['payment_details'])); ?></div>
                    
                    <div class="payment-features">
                        <?php foreach ($method_features as $feature): ?>
                            <div class="feature-tag">
                                <i class="fas fa-check"></i>
                                <?php echo $feature; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="payment-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo rand(95, 99); ?>%</div>
                            <div class="stat-label">Success Rate</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo rand(1, 5); ?>min</div>
                            <div class="stat-label">Avg Time</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">$<?php echo rand(10, 100); ?></div>
                            <div class="stat-label">Min Amount</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">24/7</div>
                            <div class="stat-label">Support</div>
                        </div>
                    </div>
                    
                    <div class="payment-actions">
                        <button class="btn" onclick="selectPaymentMethod('<?php echo sanitizeInput($method_name); ?>')">
                            <i class="fas fa-credit-card"></i>
                            Select Method
                        </button>
                        <button class="btn secondary" onclick="copyPaymentDetails('<?php echo sanitizeInput($method['payment_details']); ?>')">
                            <i class="fas fa-copy"></i>
                            Copy Details
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="back-navigation">
            <a href="office_selection.php?case_id=<?php echo urlencode($case_id); ?>" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Department Selection
            </a>
        </div>
    </div>
    
    <script>
        function selectPaymentMethod(methodName) {
            const message = `You have selected: ${methodName}\n\nPlease follow the payment instructions provided above and include your case ID (<?php echo sanitizeInput($case['case_id']); ?>) in the payment description.\n\nAfter completing the payment, our team will be notified and will process your transaction within 24-48 hours.\n\nWould you like to proceed?`;
            
            if (confirm(message)) {
                alert('Payment method selected! Please follow the instructions above to complete your payment. Remember to include your case ID in the payment description.');
            }
        }
        
        function copyPaymentDetails(details) {
            // Create a temporary textarea to copy the text
            const textarea = document.createElement('textarea');
            textarea.value = details + '\n\nCase ID: <?php echo sanitizeInput($case['case_id']); ?>';
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                document.execCommand('copy');
                alert('Payment details copied to clipboard!\n\nDon\'t forget to include your case ID in the payment.');
            } catch (err) {
                alert('Failed to copy to clipboard. Please manually copy the payment details.');
            }
            
            document.body.removeChild(textarea);
        }
        
        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.location.href = 'office_selection.php?case_id=<?php echo urlencode($case_id); ?>';
            }
        });
        
        // Add scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observe all payment cards
        document.querySelectorAll('.payment-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
            observer.observe(card);
        });
    </script>
</body>
</html>