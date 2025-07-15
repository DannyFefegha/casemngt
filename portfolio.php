<?php
// portfolio.php - Show portfolio details with KYC
require_once 'config.php';

$case_id = isset($_GET['case_id']) ? sanitizeInput($_GET['case_id']) : '';

if (empty($case_id)) {
    header("Location: index.php");
    exit;
}

// Fetch case details
$stmt = $pdo->prepare("SELECT * FROM cases WHERE case_id = ? AND status = 'activated'");
$stmt->execute([$case_id]);
$case = $stmt->fetch();

if (!$case) {
    header("Location: index.php?error=case_not_found");
    exit;
}

// Handle KYC document upload (if implemented)
$upload_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['kyc_document'])) {
    // This is a placeholder for KYC document upload
    // In a real implementation, you would handle file upload here
    $upload_message = 'KYC document upload feature will be implemented based on your hosting requirements.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio - <?php echo sanitizeInput($case['full_name']); ?></title>
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
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="0,100 1000,0 1000,100"/></svg>');
            background-size: cover;
        }
        
        .header-content {
            position: relative;
            z-index: 1;
        }
        
        .header h1 {
            font-size: 36px;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .case-id {
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            font-weight: 600;
            letter-spacing: 1px;
        }
        
        .content {
            padding: 40px;
        }
        
        .kyc-section {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 1px solid #ffeaa7;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .kyc-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f39c12, #e67e22, #d35400);
        }
        
        .kyc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .kyc-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
            font-weight: 600;
            color: #856404;
        }
        
        .kyc-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .kyc-status.pending {
            background: #f39c12;
            color: white;
        }
        
        .kyc-status.approved {
            background: #27ae60;
            color: white;
        }
        
        .kyc-status.rejected {
            background: #e74c3c;
            color: white;
        }
        
        .kyc-info {
            color: #856404;
            line-height: 1.6;
        }
        
        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .info-card {
            background: rgba(102, 126, 234, 0.05);
            padding: 25px;
            border-radius: 15px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
        }
        
        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 60px;
            height: 60px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 50%;
            transform: translate(20px, -20px);
        }
        
        .info-label {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-value {
            color: #333;
            font-size: 16px;
            line-height: 1.5;
            position: relative;
            z-index: 1;
        }
        
        .amount {
            font-size: 32px;
            font-weight: bold;
            color: #27ae60;
            text-shadow: 0 2px 4px rgba(39, 174, 96, 0.2);
        }
        
        .financial-insight-card {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #6c757d;
        }
        
        .promote-section {
            text-align: center;
            margin-top: 40px;
            padding: 40px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-radius: 15px;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }
        
        .promote-section h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 300;
        }
        
        .promote-section p {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 18px 40px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
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
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
        }
        
        .back-navigation {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .back-link {
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }
        
        .back-link:hover {
            color: #764ba2;
            transform: translateX(-5px);
        }
        
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
            padding: 20px;
            background: rgba(255,255,255,0.5);
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 28px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .portfolio-grid {
                grid-template-columns: 1fr;
            }
            
            .kyc-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .stats-bar {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1>Portfolio Dashboard</h1>
                <div class="case-id">Case ID: <?php echo sanitizeInput($case['case_id']); ?></div>
            </div>
        </div>
        
        <div class="content">
            <?php if ($upload_message): ?>
                <div class="kyc-section">
                    <div class="kyc-info">
                        <i class="fas fa-info-circle"></i> <?php echo $upload_message; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="kyc-section">
                <div class="kyc-header">
                    <div class="kyc-title">
                        <i class="fas fa-shield-alt"></i>
                        KYC Verification Status
                    </div>
                    <span class="kyc-status <?php echo $case['kyc_status']; ?>">
                        <?php echo ucfirst($case['kyc_status']); ?>
                    </span>
                </div>
                
                <div class="kyc-info">
                    <?php if ($case['kyc_status'] === 'pending'): ?>
                        <p><strong>Status:</strong> Your KYC verification is currently being reviewed by our compliance team.</p>
                        <p><strong>Expected Time:</strong> 24-48 hours</p>
                        <p><strong>Required:</strong> Government-issued ID and proof of address</p>
                    <?php elseif ($case['kyc_status'] === 'approved'): ?>
                        <p><strong>Status:</strong> Your identity has been successfully verified. You now have full access to all services.</p>
                        <p><strong>Verified On:</strong> <?php echo date('F j, Y', strtotime($case['updated_at'])); ?></p>
                    <?php else: ?>
                        <p><strong>Status:</strong> Your KYC verification was rejected. Please contact support for assistance.</p>
                        <p><strong>Action Required:</strong> Submit valid documentation or contact our support team</p>
                    <?php endif; ?>
                    
                    <?php if ($case['kyc_document']): ?>
                        <p><strong>Document:</strong> <?php echo sanitizeInput($case['kyc_document']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-value">$<?php echo number_format($case['recovered_amount'], 0); ?></div>
                    <div class="stat-label">Recovered</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo ucfirst($case['status']); ?></div>
                    <div class="stat-label">Status</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo ucfirst($case['kyc_status']); ?></div>
                    <div class="stat-label">KYC Status</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo date('M j', strtotime($case['created_at'])); ?></div>
                    <div class="stat-label">Case Opened</div>
                </div>
            </div>
            
            <div class="portfolio-grid">
                <div class="info-card">
                    <div class="info-label">
                        <i class="fas fa-user"></i>
                        Full Name
                    </div>
                    <div class="info-value"><?php echo sanitizeInput($case['full_name']); ?></div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">
                        <i class="fas fa-envelope"></i>
                        Contact Information
                    </div>
                    <div class="info-value"><?php echo nl2br(sanitizeInput($case['contact_info'])); ?></div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">
                        <i class="fas fa-exclamation-triangle"></i>
                        Spam Type
                    </div>
                    <div class="info-value"><?php echo sanitizeInput($case['spam_type']); ?></div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">
                        <i class="fas fa-building"></i>
                        Succulent Broker Name
                    </div>
                    <div class="info-value"><?php echo sanitizeInput($case['broker_name']); ?></div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">
                        <i class="fas fa-dollar-sign"></i>
                        Recovered Amount
                    </div>
                    <div class="info-value amount">$<?php echo number_format($case['recovered_amount'], 2); ?></div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">
                        <i class="fas fa-home"></i>
                        House Address
                    </div>
                    <div class="info-value"><?php echo nl2br(sanitizeInput($case['house_address'])); ?></div>
                </div>
                
                <div class="info-card financial-insight-card">
                    <div class="info-label">
                        <i class="fas fa-chart-line"></i>
                        Financial Insight
                    </div>
                    <div class="info-value"><?php echo nl2br(sanitizeInput($case['financial_insight'])); ?></div>
                </div>
            </div>
            
            <div class="promote-section">
                <h3>Ready to Proceed?</h3>
                <p>Your case has been reviewed and is ready for the next phase. Click below to promote your case and connect with our specialized departments.</p>
                
                <a href="office_selection.php?case_id=<?php echo urlencode($case['case_id']); ?>" class="btn">
                    <i class="fas fa-arrow-right"></i> Promote Case
                </a>
            </div>
            
            <div class="back-navigation">
                <a href="case_details.php?case_id=<?php echo urlencode($case['case_id']); ?>" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Back to Case Details
                </a>
            </div>
        </div>
    </div>
</body>
</html>