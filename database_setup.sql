-- database_setup.sql
-- Run this in your MySQL database first


-- Cases table
CREATE TABLE cases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    case_id VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    contact_info TEXT,
    spam_type VARCHAR(100),
    broker_name VARCHAR(255),
    recovered_amount DECIMAL(15,2) DEFAULT 0.00,
    house_address TEXT,
    financial_insight TEXT,
    status ENUM('pending', 'activated', 'declined') DEFAULT 'pending',
    kyc_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    kyc_document VARCHAR(255),
    kyc_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admins table
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Agents table
CREATE TABLE agents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    office_type ENUM('FBO', 'stock_exchange', 'SEO') NOT NULL,
    agent_name VARCHAR(255) NOT NULL,
    contact_info TEXT,
    email VARCHAR(100),
    phone VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Payment methods table
CREATE TABLE payment_methods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    method_name VARCHAR(100) NOT NULL,
    payment_details TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Activity logs table
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    case_id VARCHAR(50),
    admin_id INT,
    action VARCHAR(100),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
);

-- Insert default admin (username: admin, password: admin123)
INSERT INTO admins (username, password, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@company.com');

-- Insert sample agents
INSERT INTO agents (office_type, agent_name, contact_info, email, phone) VALUES 
('FBO', 'John Smith', 'Federal Bureau Office Agent\nSpecializes in financial investigations', 'john.smith@fbo.gov', '+1-555-0101'),
('FBO', 'Mary Johnson', 'Senior FBO Investigator\nExpert in fraud cases', 'mary.johnson@fbo.gov', '+1-555-0102'),
('stock_exchange', 'Sarah Wilson', 'Stock Exchange Intermediary\nMarket specialist', 'sarah.wilson@stockex.com', '+1-555-0201'),
('stock_exchange', 'Robert Brown', 'Senior Market Analyst\nTrading violations expert', 'robert.brown@stockex.com', '+1-555-0202'),
('SEO', 'Mike Davis', 'SEO Specialist\nDigital investigation expert', 'mike.davis@seo.com', '+1-555-0301'),
('SEO', 'Jennifer Lee', 'Senior SEO Analyst\nOnline fraud specialist', 'jennifer.lee@seo.com', '+1-555-0302');

-- Insert sample payment methods
INSERT INTO payment_methods (method_name, payment_details) VALUES 
('PayPal', 'Account: payments@escrow-company.com\nReference: Include case ID in payment notes'),
('Bitcoin (BTC)', 'Wallet Address: 1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa\nNetwork: Bitcoin Mainnet\nMin Amount: $100'),
('Ethereum (ETH)', 'Wallet Address: 0x742d35Cc6635C0532925a3b8D84c5d6d\nNetwork: Ethereum Mainnet\nGas Fee: Include extra for transaction'),
('Bank Wire Transfer', 'Bank: First National Bank\nAccount Number: 123456789\nRouting: 021000021\nAccount Name: Escrow Services LLC'),
('Zelle', 'Email: escrow@payments.com\nPhone: +1-555-ESCROW\nDaily Limit: $2,500'),
('Cash App', 'Handle: $EscrowPayments\nNote: Include case ID in payment description'),
('Venmo', 'Username: @EscrowServices\nNote: Business account for secure transactions');

-- Insert sample cases for testing
INSERT INTO cases (case_id, full_name, contact_info, spam_type, broker_name, recovered_amount, house_address, financial_insight, status, kyc_status) VALUES 
('CASE001', 'John Doe', 'Email: john.doe@email.com\nPhone: +1-555-1234', 'Investment Fraud', 'Premium Brokers LLC', 15000.00, '123 Main Street, New York, NY 10001', 'Client fell victim to fake investment platform. Recovery process initiated through legal channels.', 'activated', 'approved'),
('CASE002', 'Jane Smith', 'Email: jane.smith@email.com\nPhone: +1-555-5678', 'Romance Scam', 'Trust Investments', 8500.00, '456 Oak Avenue, Los Angeles, CA 90210', 'Romance scam involving fake dating profile. Funds transferred to offshore accounts.', 'pending', 'pending'),
('CASE003', 'Robert Johnson', 'Email: robert.j@email.com\nPhone: +1-555-9012', 'Crypto Scam', 'Digital Assets Pro', 25000.00, '789 Pine Road, Chicago, IL 60601', 'Cryptocurrency investment scam. Multiple victims identified. Recovery efforts ongoing.', 'activated', 'approved');