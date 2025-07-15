<?php
// admin_dashboard.php - Complete admin dashboard
require_once 'config.php';

// Check if user is logged in as admin
redirectIfNotAdmin();

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitizeInput($_POST['action'] ?? '');
    
    try {
        switch ($action) {
            case 'add_case':
                $case_id = sanitizeInput($_POST['case_id']);
                $full_name = sanitizeInput($_POST['full_name']);
                $contact_info = sanitizeInput($_POST['contact_info']);
                $spam_type = sanitizeInput($_POST['spam_type']);
                $broker_name = sanitizeInput($_POST['broker_name']);
                $recovered_amount = floatval($_POST['recovered_amount']);
                $house_address = sanitizeInput($_POST['house_address']);
                $financial_insight = sanitizeInput($_POST['financial_insight']);
                
                $stmt = $pdo->prepare("INSERT INTO cases (case_id, full_name, contact_info, spam_type, broker_name, recovered_amount, house_address, financial_insight) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$case_id, $full_name, $contact_info, $spam_type, $broker_name, $recovered_amount, $house_address, $financial_insight]);
                
                logActivity($pdo, $case_id, $_SESSION['admin_id'], 'Case Created', 'New case created by admin');
                $success = "Case '$case_id' added successfully!";
                break;
                
            case 'update_case':
                $case_db_id = intval($_POST['case_db_id']);
                $status = sanitizeInput($_POST['status']);
                $kyc_status = sanitizeInput($_POST['kyc_status']);
                $kyc_notes = sanitizeInput($_POST['kyc_notes']);
                
                $stmt = $pdo->prepare("UPDATE cases SET status = ?, kyc_status = ?, kyc_notes = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$status, $kyc_status, $kyc_notes, $case_db_id]);
                
                // Get case_id for logging
                $stmt = $pdo->prepare("SELECT case_id FROM cases WHERE id = ?");
                $stmt->execute([$case_db_id]);
                $case = $stmt->fetch();
                
                logActivity($pdo, $case['case_id'], $_SESSION['admin_id'], 'Case Updated', "Status: $status, KYC: $kyc_status");
                $success = "Case updated successfully!";
                break;
                
            case 'add_agent':
                $office_type = sanitizeInput($_POST['office_type']);
                $agent_name = sanitizeInput($_POST['agent_name']);
                $contact_info = sanitizeInput($_POST['contact_info']);
                $email = sanitizeInput($_POST['email']);
                $phone = sanitizeInput($_POST['phone']);
                
                $stmt = $pdo->prepare("INSERT INTO agents (office_type, agent_name, contact_info, email, phone) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$office_type, $agent_name, $contact_info, $email, $phone]);
                
                logActivity($pdo, null, $_SESSION['admin_id'], 'Agent Added', "Added agent: $agent_name to $office_type");
                $success = "Agent '$agent_name' added successfully!";
                break;
                
            case 'update_agent':
                $agent_id = intval($_POST['agent_id']);
                $status = sanitizeInput($_POST['agent_status']);
                
                $stmt = $pdo->prepare("UPDATE agents SET status = ? WHERE id = ?");
                $stmt->execute([$status, $agent_id]);
                
                logActivity($pdo, null, $_SESSION['admin_id'], 'Agent Updated', "Agent ID: $agent_id, Status: $status");
                $success = "Agent status updated successfully!";
                break;
                
            case 'add_payment':
                $method_name = sanitizeInput($_POST['method_name']);
                $payment_details = sanitizeInput($_POST['payment_details']);
                
                $stmt = $pdo->prepare("INSERT INTO payment_methods (method_name, payment_details) VALUES (?, ?)");
                $stmt->execute([$method_name, $payment_details]);
                
                logActivity($pdo, null, $_SESSION['admin_id'], 'Payment Method Added', "Added: $method_name");
                $success = "Payment method '$method_name' added successfully!";
                break;
                
            case 'update_payment':
                $payment_id = intval($_POST['payment_id']);
                $status = sanitizeInput($_POST['payment_status']);
                
                $stmt = $pdo->prepare("UPDATE payment_methods SET status = ? WHERE id = ?");
                $stmt->execute([$status, $payment_id]);
                
                logActivity($pdo, null, $_SESSION['admin_id'], 'Payment Method Updated', "Payment ID: $payment_id, Status: $status");
                $success = "Payment method status updated successfully!";
                break;
                
            default:
                $error = "Unknown action specified.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch dashboard data
try {
    // Get statistics
    $stats = [
        'total_cases' => $pdo->query("SELECT COUNT(*) FROM cases")->fetchColumn(),
        'active_cases' => $pdo->query("SELECT COUNT(*) FROM cases WHERE status = 'activated'")->fetchColumn(),
        'pending_kyc' => $pdo->query("SELECT COUNT(*) FROM cases WHERE kyc_status = 'pending'")->fetchColumn(),
        'total_agents' => $pdo->query("SELECT COUNT(*) FROM agents WHERE status = 'active'")->fetchColumn(),
        'total_recovered' => $pdo->query("SELECT SUM(recovered_amount) FROM cases WHERE status = 'activated'")->fetchColumn()
    ];
    
    // Get recent cases
    $recent_cases = $pdo->query("SELECT * FROM cases ORDER BY created_at DESC LIMIT 10")->fetchAll();
    
    // Get all cases for management
    $all_cases = $pdo->query("SELECT * FROM cases ORDER BY created_at DESC")->fetchAll();
    
    // Get agents
    $agents = $pdo->query("SELECT * FROM agents ORDER BY office_type, agent_name")->fetchAll();
    
    // Get payment methods
    $payment_methods = $pdo->query("SELECT * FROM payment_methods ORDER BY method_name")->fetchAll();
    
    // Get recent activity
    $recent_activity = $pdo->query("SELECT al.*, c.case_id, a.username FROM activity_logs al LEFT JOIN cases c ON al.case_id = c.case_id LEFT JOIN admins a ON al.admin_id = a.id ORDER BY al.created_at DESC LIMIT 20")->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error fetching data: " . $e->getMessage();
    $stats = ['total_cases' => 0, 'active_cases' => 0, 'pending_kyc' => 0, 'total_agents' => 0, 'total_recovered' => 0];
    $recent_cases = [];
    $all_cases = [];
    $agents = [];
    $payment_methods = [];
    $recent_activity = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Case Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h2 {
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .admin-info {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            display: block;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .menu-item:hover, .menu-item.active {
            background: rgba(255,255,255,0.1);
            border-left-color: #3498db;
            padding-left: 30px;
        }
        
        .menu-item i {
            width: 20px;
            margin-right: 15px;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 20px;
            width: calc(100% - 280px);
        }
        
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #2c3e50;
            font-weight: 300;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .btn {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .btn.danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        
        .btn.danger:hover {
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }
        
        .btn.success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }
        
        .btn.success:hover {
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
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
            background: var(--accent-color, #3498db);
        }
        
        .stat-icon {
            font-size: 36px;
            color: var(--accent-color, #3498db);
            margin-bottom: 15px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .content-tabs {
            margin-bottom: 20px;
        }
        
        .tab-buttons {
            display: flex;
            gap: 5px;
            margin-bottom: 20px;
            background: white;
            padding: 5px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .tab-btn {
            flex: 1;
            padding: 15px 20px;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .tab-btn.active {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
        }
        
        .tab-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #3498db;
        }
        
        textarea {
            height: 100px;
            resize: vertical;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .table tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-activated {
            background: #d4edda;
            color: #155724;
        }
        
        .status-declined {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .quick-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .quick-actions .btn {
            font-size: 12px;
            padding: 6px 12px;
        }
        
        .search-box {
            margin-bottom: 20px;
        }
        
        .search-box input {
            max-width: 300px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .header {
                padding: 15px 20px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .tab-buttons {
                flex-direction: column;
            }
        }
        
        .mobile-menu-btn {
            display: none;
            background: #3498db;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
        }
        
        .activity-item {
            padding: 15px;
            border-left: 4px solid #3498db;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 0 6px 6px 0;
        }
        
        .activity-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .activity-action {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .activity-time {
            color: #7f8c8d;
            font-size: 12px;
        }
        
        .activity-details {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-shield-alt"></i> Admin Panel</h2>
                <div class="admin-info">
                    Welcome, <?php echo sanitizeInput($_SESSION['admin_username']); ?>
                </div>
            </div>
            
            <div class="sidebar-menu">
                <a href="#dashboard" class="menu-item active" onclick="showTab('dashboard')">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="#cases" class="menu-item" onclick="showTab('cases')">
                    <i class="fas fa-folder-open"></i> Manage Cases
                </a>
                <a href="#agents" class="menu-item" onclick="showTab('agents')">
                    <i class="fas fa-users"></i> Manage Agents
                </a>
                <a href="#payments" class="menu-item" onclick="showTab('payments')">
                    <i class="fas fa-credit-card"></i> Payment Methods
                </a>
                <a href="#activity" class="menu-item" onclick="showTab('activity')">
                    <i class="fas fa-history"></i> Activity Log
                </a>
            </div>
        </nav>
        
        <main class="main-content">
            <div class="header">
                <button class="mobile-menu-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Case Management Dashboard</h1>
                <div class="header-actions">
                    <span style="color: #7f8c8d; font-size: 14px;">
                        Last login: <?php echo date('M j, Y g:i A', $_SESSION['login_time']); ?>
                    </span>
                    <a href="admin_logout.php" class="btn danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <?php if ($success): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Dashboard Tab -->
            <div id="dashboard-tab" class="tab-content active">
                <div class="stats-grid">
                    <div class="stat-card" style="--accent-color: #3498db;">
                        <div class="stat-icon">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['total_cases']); ?></div>
                        <div class="stat-label">Total Cases</div>
                    </div>
                    
                    <div class="stat-card" style="--accent-color: #27ae60;">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['active_cases']); ?></div>
                        <div class="stat-label">Active Cases</div>
                    </div>
                    
                    <div class="stat-card" style="--accent-color: #f39c12;">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['pending_kyc']); ?></div>
                        <div class="stat-label">Pending KYC</div>
                    </div>
                    
                    <div class="stat-card" style="--accent-color: #9b59b6;">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['total_agents']); ?></div>
                        <div class="stat-label">Active Agents</div>
                    </div>
                    
                    <div class="stat-card" style="--accent-color: #e67e22;">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-value">$<?php echo number_format($stats['total_recovered'] ?: 0); ?></div>
                        <div class="stat-label">Total Recovered</div>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <h3 class="section-title">
                            <i class="fas fa-plus-circle"></i>
                            Quick Add Case
                        </h3>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_case">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Case ID:</label>
                                    <input type="text" name="case_id" required placeholder="e.g., CASE12345" value="<?php echo generateCaseId(); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Full Name:</label>
                                    <input type="text" name="full_name" required placeholder="Client's full name">
                                </div>
                                <div class="form-group">
                                    <label>Spam Type:</label>
                                    <select name="spam_type" required>
                                        <option value="">Select type...</option>
                                        <option value="Investment Fraud">Investment Fraud</option>
                                        <option value="Romance Scam">Romance Scam</option>
                                        <option value="Crypto Scam">Crypto Scam</option>
                                        <option value="Phishing">Phishing</option>
                                        <option value="Business Email Compromise">Business Email Compromise</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Recovered Amount:</label>
                                    <input type="number" step="0.01" name="recovered_amount" placeholder="0.00">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Contact Information:</label>
                                <textarea name="contact_info" placeholder="Email, phone, etc."></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Broker Name:</label>
                                <input type="text" name="broker_name" placeholder="Associated broker/company">
                            </div>
                            
                            <div class="form-group">
                                <label>House Address:</label>
                                <textarea name="house_address" placeholder="Client's address"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Financial Insight:</label>
                                <textarea name="financial_insight" placeholder="Analysis and recovery details"></textarea>
                            </div>
                            
                            <button type="submit" class="btn success">
                                <i class="fas fa-plus"></i> Add Case
                            </button>
                        </form>
                    </div>
                    
                    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <h3 class="section-title">
                            <i class="fas fa-clock"></i>
                            Recent Cases
                        </h3>
                        
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach (array_slice($recent_cases, 0, 5) as $case): ?>
                                <div style="padding: 15px; border-bottom: 1px solid #eee; margin-bottom: 10px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                        <strong><?php echo sanitizeInput($case['case_id']); ?></strong>
                                        <span class="status-badge status-<?php echo $case['status']; ?>">
                                            <?php echo ucfirst($case['status']); ?>
                                        </span>
                                    </div>
                                    <div style="color: #666; font-size: 14px; margin-bottom: 5px;">
                                        <?php echo sanitizeInput($case['full_name']); ?>
                                    </div>
                                    <div style="color: #7f8c8d; font-size: 12px;">
                                        <?php echo date('M j, Y', strtotime($case['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Cases Tab -->
            <div id="cases-tab" class="tab-content">
                <h3 class="section-title">
                    <i class="fas fa-folder-open"></i>
                    Manage Cases
                </h3>
                
                <div class="search-box">
                    <input type="text" id="caseSearch" placeholder="Search cases..." onkeyup="filterTable('casesTable', this.value)">
                </div>
                
                <div class="table-container">
                    <table class="table" id="casesTable">
                        <thead>
                            <tr>
                                <th>Case ID</th>
                                <th>Full Name</th>
                                <th>Spam Type</th>
                                <th>Status</th>
                                <th>KYC Status</th>
                                <th>Amount</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_cases as $case): ?>
                                <tr>
                                    <td><?php echo sanitizeInput($case['case_id']); ?></td>
                                    <td><?php echo sanitizeInput($case['full_name']); ?></td>
                                    <td><?php echo sanitizeInput($case['spam_type']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $case['status']; ?>">
                                            <?php echo ucfirst($case['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $case['kyc_status']; ?>">
                                            <?php echo ucfirst($case['kyc_status']); ?>
                                        </span>
                                    </td>
                                    <td>$<?php echo number_format($case['recovered_amount'], 2); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($case['created_at'])); ?></td>
                                    <td>
                                        <div class="quick-actions">
                                            <button class="btn" onclick="editCase(<?php echo $case['id']; ?>, '<?php echo $case['status']; ?>', '<?php echo $case['kyc_status']; ?>', '<?php echo sanitizeInput($case['kyc_notes']); ?>')">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Agents Tab -->
            <div id="agents-tab" class="tab-content">
                <h3 class="section-title">
                    <i class="fas fa-users"></i>
                    Manage Agents
                </h3>
                
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                    <div>
                        <h4>Add New Agent</h4>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_agent">
                            <div class="form-group">
                                <label>Office Type:</label>
                                <select name="office_type" required>
                                    <option value="">Select office...</option>
                                    <option value="FBO">FBO</option>
                                    <option value="stock_exchange">Stock Exchange Intermediary</option>
                                    <option value="SEO">SEO</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Agent Name:</label>
                                <input type="text" name="agent_name" required>
                            </div>
                            <div class="form-group">
                                <label>Email:</label>
                                <input type="email" name="email">
                            </div>
                            <div class="form-group">
                                <label>Phone:</label>
                                <input type="text" name="phone">
                            </div>
                            <div class="form-group">
                                <label>Contact Info:</label>
                                <textarea name="contact_info"></textarea>
                            </div>
                            <button type="submit" class="btn success">
                                <i class="fas fa-plus"></i> Add Agent
                            </button>
                        </form>
                    </div>
                    
                    <div>
                        <h4>Current Agents</h4>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Office</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($agents as $agent): ?>
                                        <tr>
                                            <td><?php echo sanitizeInput($agent['office_type']); ?></td>
                                            <td><?php echo sanitizeInput($agent['agent_name']); ?></td>
                                            <td><?php echo sanitizeInput($agent['email']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $agent['status']; ?>">
                                                    <?php echo ucfirst($agent['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn" onclick="toggleAgentStatus(<?php echo $agent['id']; ?>, '<?php echo $agent['status']; ?>')">
                                                    <i class="fas fa-toggle-<?php echo $agent['status'] === 'active' ? 'on' : 'off'; ?>"></i>
                                                    <?php echo $agent['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Methods Tab -->
            <div id="payments-tab" class="tab-content">
                <h3 class="section-title">
                    <i class="fas fa-credit-card"></i>
                    Manage Payment Methods
                </h3>
                
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                    <div>
                        <h4>Add Payment Method</h4>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_payment">
                            <div class="form-group">
                                <label>Method Name:</label>
                                <input type="text" name="method_name" required placeholder="e.g., PayPal">
                            </div>
                            <div class="form-group">
                                <label>Payment Details:</label>
                                <textarea name="payment_details" required placeholder="Account details, instructions, etc."></textarea>
                            </div>
                            <button type="submit" class="btn success">
                                <i class="fas fa-plus"></i> Add Payment Method
                            </button>
                        </form>
                    </div>
                    
                    <div>
                        <h4>Current Payment Methods</h4>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Method Name</th>
                                        <th>Details</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payment_methods as $method): ?>
                                        <tr>
                                            <td><?php echo sanitizeInput($method['method_name']); ?></td>
                                            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                                <?php echo substr(sanitizeInput($method['payment_details']), 0, 50); ?>...
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $method['status']; ?>">
                                                    <?php echo ucfirst($method['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn" onclick="togglePaymentStatus(<?php echo $method['id']; ?>, '<?php echo $method['status']; ?>')">
                                                    <i class="fas fa-toggle-<?php echo $method['status'] === 'active' ? 'on' : 'off'; ?>"></i>
                                                    <?php echo $method['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Activity Tab -->
            <div id="activity-tab" class="tab-content">
                <h3 class="section-title">
                    <i class="fas fa-history"></i>
                    Activity Log
                </h3>
                
                <div style="max-height: 600px; overflow-y: auto;">
                    <?php foreach ($recent_activity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-header">
                                <span class="activity-action"><?php echo sanitizeInput($activity['action']); ?></span>
                                <span class="activity-time"><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></span>
                            </div>
                            <div class="activity-details">
                                <?php if ($activity['case_id']): ?>
                                    Case: <?php echo sanitizeInput($activity['case_id']); ?> |
                                <?php endif; ?>
                                <?php if ($activity['username']): ?>
                                    Admin: <?php echo sanitizeInput($activity['username']); ?> |
                                <?php endif; ?>
                                <?php echo sanitizeInput($activity['details']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Edit Case Modal (Hidden Form) -->
    <div id="editCaseForm" style="display: none;">
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_case">
            <input type="hidden" name="case_db_id" id="editCaseId">
            <input type="hidden" name="status" id="editStatus">
            <input type="hidden" name="kyc_status" id="editKycStatus">
            <input type="hidden" name="kyc_notes" id="editKycNotes">
        </form>
    </div>
    
    <!-- Agent Status Form -->
    <div id="agentStatusForm" style="display: none;">
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_agent">
            <input type="hidden" name="agent_id" id="agentId">
            <input type="hidden" name="agent_status" id="agentStatus">
        </form>
    </div>
    
    <!-- Payment Status Form -->
    <div id="paymentStatusForm" style="display: none;">
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_payment">
            <input type="hidden" name="payment_id" id="paymentId">
            <input type="hidden" name="payment_status" id="paymentStatus">
        </form>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all menu items
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked menu item
            event.target.classList.add('active');
        }
        
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }
        
        function editCase(id, status, kycStatus, kycNotes) {
            const newStatus = prompt(`Current status: ${status}\n\nEnter new status (pending/activated/declined):`, status);
            if (newStatus && ['pending', 'activated', 'declined'].includes(newStatus)) {
                const newKycStatus = prompt(`Current KYC status: ${kycStatus}\n\nEnter new KYC status (pending/approved/rejected):`, kycStatus);
                if (newKycStatus && ['pending', 'approved', 'rejected'].includes(newKycStatus)) {
                    const newKycNotes = prompt('KYC Notes:', kycNotes || '');
                    
                    document.getElementById('editCaseId').value = id;
                    document.getElementById('editStatus').value = newStatus;
                    document.getElementById('editKycStatus').value = newKycStatus;
                    document.getElementById('editKycNotes').value = newKycNotes || '';
                    document.querySelector('#editCaseForm form').submit();
                }
            }
        }
        
        function toggleAgentStatus(id, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this agent?`)) {
                document.getElementById('agentId').value = id;
                document.getElementById('agentStatus').value = newStatus;
                document.querySelector('#agentStatusForm form').submit();
            }
        }
        
        function togglePaymentStatus(id, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this payment method?`)) {
                document.getElementById('paymentId').value = id;
                document.getElementById('paymentStatus').value = newStatus;
                document.querySelector('#paymentStatusForm form').submit();
            }
        }
        
        function filterTable(tableId, searchTerm) {
            const table = document.getElementById(tableId);
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().includes(searchTerm.toLowerCase())) {
                        found = true;
                        break;
                    }
                }
                
                row.style.display = found ? '' : 'none';
            }
        }
        
        // Auto-refresh every 5 minutes
        setInterval(() => {
            if (confirm('Refresh dashboard data?')) {
                location.reload();
            }
        }, 300000);
        
        // Show current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleString();
            document.title = `Admin Dashboard - ${timeString}`;
        }
        
        setInterval(updateTime, 1000);
        updateTime();
    </script>
</body>
</html>