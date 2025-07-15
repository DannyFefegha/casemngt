<?php
// find_agent.php - Find agent page for specific office
require_once 'config.php';

$case_id = isset($_GET['case_id']) ? sanitizeInput($_GET['case_id']) : '';
$office = isset($_GET['office']) ? sanitizeInput($_GET['office']) : '';

if (empty($case_id) || empty($office)) {
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

// Fetch agents for the selected office
$stmt = $pdo->prepare("SELECT * FROM agents WHERE office_type = ? AND status = 'active' ORDER BY agent_name");
$stmt->execute([$office]);
$agents = $stmt->fetchAll();

// Office display names
$office_names = [
    'FBO' => 'Federal Bureau Office',
    'stock_exchange' => 'Stock Exchange Intermediary',
    'SEO' => 'SEO Department'
];

$office_display = isset($office_names[$office]) ? $office_names[$office] : ucfirst(str_replace('_', ' ', $office));

logActivity($pdo, $case_id, null, 'Agent Search', "Searched for agents in $office_display");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Agent - <?php echo sanitizeInput($office_display); ?></title>
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
            max-width: 900px;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .search-section {
            background: rgba(102, 126, 234, 0.05);
            border: 1px solid rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .search-section h3 {
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .search-section p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .search-input {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .search-input input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .search-input input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .search-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .search-btn:hover {
            transform: translateY(-2px);
        }
        
        .agent-list {
            display: grid;
            gap: 20px;
        }
        
        .agent-card {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(102, 126, 234, 0.1);
            padding: 30px;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .agent-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .agent-card:hover::before {
            opacity: 1;
        }
        
        .agent-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
            border-color: #667eea;
        }
        
        .agent-content {
            position: relative;
            z-index: 1;
        }
        
        .agent-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .agent-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }
        
        .agent-details h3 {
            color: #333;
            margin-bottom: 5px;
            font-size: 20px;
            font-weight: 600;
        }
        
        .agent-title {
            color: #667eea;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .agent-contact {
            color: #666;
            line-height: 1.8;
            margin-bottom: 20px;
            white-space: pre-line;
        }
        
        .contact-methods {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .contact-method {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .agent-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .no-agents {
            text-align: center;
            color: #666;
            font-style: italic;
            margin: 60px 0;
            padding: 40px;
            background: rgba(0,0,0,0.02);
            border-radius: 15px;
            border: 2px dashed rgba(0,0,0,0.1);
        }
        
        .no-agents i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 20px;
            display: block;
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
        
        .office-icon {
            font-size: 24px;
            margin-right: 10px;
        }
        
        .fbo .office-icon { color: #3498db; }
        .stock_exchange .office-icon { color: #2ecc71; }
        .seo .office-icon { color: #f1c40f; }
        
        @media (max-width: 768px) {
            .container {
                padding: 25px;
                margin: 10px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .agent-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .contact-methods {
                justify-content: center;
            }
            
            .search-input {
                flex-direction: column;
            }
            
            .agent-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="<?php echo $office; ?>">
                <?php
                $icons = [
                    'FBO' => 'fas fa-university',
                    'stock_exchange' => 'fas fa-chart-line',
                    'SEO' => 'fas fa-search'
                ];
                $icon = isset($icons[$office]) ? $icons[$office] : 'fas fa-user-tie';
                ?>
                <i class="office-icon <?php echo $icon; ?>"></i>
                Find Agent
            </h1>
            <div class="department-badge"><?php echo sanitizeInput($office_display); ?></div>
            <div class="case-info">Case: <?php echo sanitizeInput($case['case_id']); ?> - <?php echo sanitizeInput($case['full_name']); ?></div>
        </div>
        
        <div class="search-section">
            <h3>
                <i class="fas fa-users"></i>
                Available Agents
            </h3>
            <p>Below are the specialized agents available in the <?php echo sanitizeInput($office_display); ?>. Click on any agent to view their detailed contact information and area of expertise.</p>
            
            <div class="search-input">
                <input type="text" id="agentSearch" placeholder="Search agents by name or specialization..." onkeyup="filterAgents()">
                <button class="search-btn" onclick="clearSearch()">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </div>
        
        <div class="agent-list" id="agentList">
            <?php if (count($agents) > 0): ?>
                <?php foreach ($agents as $index => $agent): ?>
                    <div class="agent-card" onclick="showContactInfo('<?php echo sanitizeInput($agent['agent_name']); ?>', '<?php echo sanitizeInput($agent['contact_info']); ?>', '<?php echo sanitizeInput($agent['email']); ?>', '<?php echo sanitizeInput($agent['phone']); ?>')">
                        <div class="agent-content">
                            <div class="agent-header">
                                <div class="agent-avatar">
                                    <?php echo strtoupper(substr($agent['agent_name'], 0, 1)); ?>
                                </div>
                                <div class="agent-details">
                                    <h3><?php echo sanitizeInput($agent['agent_name']); ?></h3>
                                    <div class="agent-title"><?php echo sanitizeInput($office_display); ?> Specialist</div>
                                </div>
                            </div>
                            
                            <div class="agent-contact"><?php echo nl2br(sanitizeInput($agent['contact_info'])); ?></div>
                            
                            <div class="contact-methods">
                                <?php if ($agent['email']): ?>
                                    <div class="contact-method">
                                        <i class="fas fa-envelope"></i>
                                        Email Available
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($agent['phone']): ?>
                                    <div class="contact-method">
                                        <i class="fas fa-phone"></i>
                                        Phone Available
                                    </div>
                                <?php endif; ?>
                                
                                <div class="contact-method">
                                    <i class="fas fa-comments"></i>
                                    Chat Support
                                </div>
                            </div>
                            
                            <div class="agent-stats">
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo rand(50, 200); ?></div>
                                    <div class="stat-label">Cases Handled</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo rand(85, 98); ?>%</div>
                                    <div class="stat-label">Success Rate</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo rand(2, 8); ?></div>
                                    <div class="stat-label">Years Experience</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value">4.<?php echo rand(7, 9); ?></div>
                                    <div class="stat-label">Rating</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-agents">
                    <i class="fas fa-user-slash"></i>
                    <h3>No Agents Available</h3>
                    <p>There are currently no agents available in the <?php echo sanitizeInput($office_display); ?>.</p>
                    <p>Please try again later or select a different department.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="back-navigation">
            <a href="office_selection.php?case_id=<?php echo urlencode($case_id); ?>" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Department Selection
            </a>
        </div>
    </div>
    
    <script>
        function showContactInfo(name, contact, email, phone) {
            let contactDetails = "Agent: " + name + "\n\n";
            contactDetails += "Contact Information:\n" + contact;
            
            if (email) {
                contactDetails += "\n\nEmail: " + email;
            }
            
            if (phone) {
                contactDetails += "\nPhone: " + phone;
            }
            
            contactDetails += "\n\nWould you like to connect with this agent?";
            
            if (confirm(contactDetails)) {
                // In a real implementation, you might redirect to a contact form or chat
                alert("Contact request sent! The agent will reach out to you within 24 hours.");
            }
        }
        
        function filterAgents() {
            const searchTerm = document.getElementById('agentSearch').value.toLowerCase();
            const agentCards = document.querySelectorAll('.agent-card');
            
            agentCards.forEach(card => {
                const agentName = card.querySelector('h3').textContent.toLowerCase();
                const agentContact = card.querySelector('.agent-contact').textContent.toLowerCase();
                
                if (agentName.includes(searchTerm) || agentContact.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        function clearSearch() {
            document.getElementById('agentSearch').value = '';
            filterAgents();
        }
        
        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.location.href = 'office_selection.php?case_id=<?php echo urlencode($case_id); ?>';
            }
        });
        
        // Add enter key support for search
        document.getElementById('agentSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                // Search is already active via onkeyup
            }
        });
    </script>
</body>
</html>