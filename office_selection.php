<?php
// office_selection.php - Office selection page
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

logActivity($pdo, $case_id, null, 'Office Selection Accessed', 'User accessed office selection page');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Selection - Case <?php echo sanitizeInput($case['case_id']); ?></title>
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
            padding: 50px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .header {
            margin-bottom: 50px;
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 15px;
            font-size: 36px;
            font-weight: 300;
        }
        
        .case-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 25px;
            display: inline-block;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }
        
        .subtitle {
            color: #666;
            font-size: 18px;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .office-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }
        
        .office-option {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid transparent;
            padding: 40px 25px;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.4s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        
        .office-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 1;
        }
        
        .office-option:hover::before {
            opacity: 1;
        }
        
        .office-option:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
            border-color: #667eea;
        }
        
        .office-content {
            position: relative;
            z-index: 2;
            transition: color 0.4s ease;
        }
        
        .office-option:hover .office-content {
            color: white;
        }
        
        .office-icon {
            font-size: 48px;
            margin-bottom: 20px;
            color: #667eea;
            transition: all 0.4s ease;
        }
        
        .office-option:hover .office-icon {
            color: white;
            transform: scale(1.1);
        }
        
        .office-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
            transition: color 0.4s ease;
        }
        
        .office-desc {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
            transition: color 0.4s ease;
        }
        
        .office-features {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(0,0,0,0.1);
            transition: border-color 0.4s ease;
        }
        
        .office-option:hover .office-features {
            border-color: rgba(255,255,255,0.3);
        }
        
        .feature-item {
            font-size: 12px;
            margin: 5px 0;
            opacity: 0.8;
        }
        
        .fbo {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, rgba(41, 128, 185, 0.1) 100%);
        }
        
        .fbo .office-icon {
            color: #3498db;
        }
        
        .stock {
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.1) 0%, rgba(39, 174, 96, 0.1) 100%);
        }
        
        .stock .office-icon {
            color: #2ecc71;
        }
        
        .seo {
            background: linear-gradient(135deg, rgba(241, 196, 15, 0.1) 0%, rgba(243, 156, 18, 0.1) 100%);
        }
        
        .seo .office-icon {
            color: #f1c40f;
        }
        
        .escrow {
            background: linear-gradient(135deg, rgba(155, 89, 182, 0.1) 0%, rgba(142, 68, 173, 0.1) 100%);
        }
        
        .escrow .office-icon {
            color: #9b59b6;
        }
        
        .back-navigation {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .back-link {
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            font-size: 16px;
        }
        
        .back-link:hover {
            color: #764ba2;
            transform: translateX(-5px);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .header h1 {
                font-size: 28px;
            }
            
            .office-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .office-option {
                padding: 30px 20px;
            }
            
            .office-icon {
                font-size: 40px;
            }
        }
        
        .info-banner {
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.2);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 40px;
            color: #667eea;
        }
        
        .info-banner h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .info-banner p {
            margin: 0;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Select Department</h1>
            <div class="case-info">Case: <?php echo sanitizeInput($case['case_id']); ?> - <?php echo sanitizeInput($case['full_name']); ?></div>
            <p class="subtitle">Choose the appropriate department to handle your case. Each department specializes in different aspects of financial recovery and investigation.</p>
        </div>
        
        <div class="info-banner">
            <h3><i class="fas fa-info-circle"></i> Department Selection</h3>
            <p>Each department has specialized agents and tools to help with your specific case type. Select the department that best matches your needs or the nature of your case.</p>
        </div>
        
        <div class="office-grid">
            <a href="find_agent.php?case_id=<?php echo urlencode($case_id); ?>&office=FBO" class="office-option fbo">
                <div class="office-content">
                    <div class="office-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="office-title">FBO</div>
                    <div class="office-desc">Federal Bureau Office specializing in financial crimes, fraud investigation, and regulatory compliance.</div>
                    <div class="office-features">
                        <div class="feature-item">• Federal Investigation</div>
                        <div class="feature-item">• Fraud Analysis</div>
                        <div class="feature-item">• Legal Compliance</div>
                        <div class="feature-item">• Asset Recovery</div>
                    </div>
                </div>
            </a>
            
            <a href="find_agent.php?case_id=<?php echo urlencode($case_id); ?>&office=stock_exchange" class="office-option stock">
                <div class="office-content">
                    <div class="office-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="office-title">Stock Exchange Intermediary</div>
                    <div class="office-desc">Financial market specialists handling trading violations, market manipulation, and securities fraud.</div>
                    <div class="office-features">
                        <div class="feature-item">• Market Analysis</div>
                        <div class="feature-item">• Trading Violations</div>
                        <div class="feature-item">• Securities Recovery</div>
                        <div class="feature-item">• Investment Fraud</div>
                    </div>
                </div>
            </a>
            
            <a href="find_agent.php?case_id=<?php echo urlencode($case_id); ?>&office=SEO" class="office-option seo">
                <div class="office-content">
                    <div class="office-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="office-title">SEO Department</div>
                    <div class="office-desc">Digital investigation experts specializing in online fraud, digital assets, and cyber crime recovery.</div>
                    <div class="office-features">
                        <div class="feature-item">• Digital Forensics</div>
                        <div class="feature-item">• Online Fraud</div>
                        <div class="feature-item">• Crypto Recovery</div>
                        <div class="feature-item">• Cyber Investigation</div>
                    </div>
                </div>
            </a>
            
            <a href="payment_methods.php?case_id=<?php echo urlencode($case_id); ?>" class="office-option escrow">
                <div class="office-content">
                    <div class="office-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="office-title">Escrow Department</div>
                    <div class="office-desc">Secure payment processing and fund management with multiple verified payment methods and protection protocols.</div>
                    <div class="office-features">
                        <div class="feature-item">• Secure Payments</div>
                        <div class="feature-item">• Fund Protection</div>
                        <div class="feature-item">• Multiple Methods</div>
                        <div class="feature-item">• Transaction Security</div>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="back-navigation">
            <a href="portfolio.php?case_id=<?php echo urlencode($case_id); ?>" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Portfolio
            </a>
        </div>
    </div>
    
    <script>
        // Add click tracking
        document.querySelectorAll('.office-option').forEach(option => {
            option.addEventListener('click', function(e) {
                // Add a small delay for the animation to complete
                e.preventDefault();
                const url = this.href;
                
                setTimeout(() => {
                    window.location.href = url;
                }, 200);
            });
        });
        
        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.location.href = 'portfolio.php?case_id=<?php echo urlencode($case_id); ?>';
            }
        });
    </script>
</body>
</html>