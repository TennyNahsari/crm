-- =============================================================
-- FlowCRM Complete Database Initialization Script (PostgreSQL)
-- =============================================================
-- Script ini membuat struktur database Master (crm_master) dan Tenant (crm)
-- beserta data awal (seeders) agar aplikasi siap digunakan.
--
-- CARA MENJALANKAN DARI CMD WINDOWS / TERMINAL:
-- 1. PostgreSQL (Rekomendasi Utama):
--    psql -U postgres -h localhost -f init.sql
--    (atau psql -U crm -h localhost -f init.sql)
--
-- Akun Login Default Awal (Password: password123)
-- Admin: admin@flowcrm.test
-- Sales 1: sales1@flowcrm.test
-- Sales 2: sales2@flowcrm.test
-- Marketing: marketing@flowcrm.test
-- Manager: manager@flowcrm.test
--
-- =============================================================

-- -------------------------------------------------------------
-- 1. PEMBUATAN DATABASE
-- -------------------------------------------------------------
CREATE DATABASE crm_master;
CREATE DATABASE crm;

-- -------------------------------------------------------------
-- 2. MASTER DATABASE (crm_master)
-- -------------------------------------------------------------
\c crm_master;

CREATE TABLE IF NOT EXISTS companies (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    database_name VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    subscription_status VARCHAR(50) DEFAULT 'trial',
    subscription_expires_at TIMESTAMP DEFAULT NULL,
    max_users INTEGER DEFAULT 10,
    max_customers INTEGER DEFAULT 1000,
    settings JSONB DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_companies_slug ON companies(slug);
CREATE INDEX IF NOT EXISTS idx_companies_database_name ON companies(database_name);
CREATE INDEX IF NOT EXISTS idx_companies_is_active ON companies(is_active);

CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP DEFAULT NULL,
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_master_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_master_users_company_id ON users(company_id);
CREATE INDEX IF NOT EXISTS idx_master_users_company_active ON users(company_id, is_active);

CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT DEFAULT NULL REFERENCES users(id) ON DELETE CASCADE,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_master_sessions_user_id ON sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_master_sessions_last_activity ON sessions(last_activity);

-- SEED DATA MASTER DATABASE
INSERT INTO companies (id, name, slug, database_name, is_active, subscription_status, max_users, max_customers, created_at, updated_at)
VALUES 
(1, 'Main Company', 'main-company', 'crm', TRUE, 'active', 100, 10000, NOW(), NOW()),
(2, 'EcoGreen', 'ecogreen', 'crm_ecogreen', TRUE, 'trial', 10, 1000, NOW(), NOW())
ON CONFLICT (id) DO NOTHING;

-- -------------------------------------------------------------
-- Akun Login Default Awal (Password: password123)
-- Admin: admin@flowcrm.test
-- Sales 1: sales1@flowcrm.test
-- Sales 2: sales2@flowcrm.test
-- Marketing: marketing@flowcrm.test
-- Manager: manager@flowcrm.test
-- -------------------------------------------------------------
INSERT INTO users (id, company_id, name, email, password, is_active, created_at, updated_at)
VALUES 
(1, 1, 'Admin System', 'admin@flowcrm.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAWefxeKo', TRUE, NOW(), NOW()),
(2, 1, 'Budi Santoso', 'sales1@flowcrm.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAWefxeKo', TRUE, NOW(), NOW()),
(3, 1, 'Siti Rahmawati', 'sales2@flowcrm.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAWefxeKo', TRUE, NOW(), NOW()),
(4, 1, 'Andi Marketing', 'marketing@flowcrm.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAWefxeKo', TRUE, NOW(), NOW()),
(5, 1, 'Manager Utama', 'manager@flowcrm.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAWefxeKo', TRUE, NOW(), NOW()),
(6, 2, 'Andhia', 'andhia@ecogreen.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAWefxeKo', TRUE, NOW(), NOW())
ON CONFLICT (id) DO NOTHING;

SELECT setval('companies_id_seq', (SELECT MAX(id) FROM companies));
SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));


-- -------------------------------------------------------------
-- 3. TENANT DATABASE (crm)
-- -------------------------------------------------------------
\c crm;

CREATE TABLE IF NOT EXISTS user_profiles (
    id BIGSERIAL PRIMARY KEY,
    master_user_id BIGINT UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    avatar_url VARCHAR(500) DEFAULT NULL,
    role VARCHAR(20) DEFAULT 'sales' CHECK (role IN ('admin', 'sales', 'marketing', 'manager')),
    permissions JSONB DEFAULT NULL,
    language VARCHAR(10) DEFAULT 'id',
    timezone VARCHAR(50) DEFAULT 'Asia/Jakarta',
    notifications JSONB DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_user_profiles_master_user_id ON user_profiles(master_user_id);
CREATE INDEX IF NOT EXISTS idx_user_profiles_email ON user_profiles(email);
CREATE INDEX IF NOT EXISTS idx_user_profiles_role ON user_profiles(role);
CREATE INDEX IF NOT EXISTS idx_user_profiles_is_active ON user_profiles(is_active);

CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'sales' CHECK (role IN ('admin', 'sales', 'marketing', 'manager')),
    is_active BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_sessions_last_activity ON sessions(last_activity);

CREATE TABLE IF NOT EXISTS cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER DEFAULT NULL,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_jobs_queue ON jobs(queue);

CREATE TABLE IF NOT EXISTS job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL,
    failed_jobs INTEGER NOT NULL,
    failed_job_ids TEXT NOT NULL,
    options TEXT DEFAULT NULL,
    cancelled_at INTEGER DEFAULT NULL,
    created_at INTEGER NOT NULL,
    finished_at INTEGER DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS failed_jobs (
    id BIGSERIAL PRIMARY KEY,
    uuid VARCHAR(255) UNIQUE NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT DEFAULT NULL,
    last_used_at TIMESTAMP DEFAULT NULL,
    expires_at TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_pat_tokenable ON personal_access_tokens(tokenable_type, tokenable_id);

CREATE TABLE IF NOT EXISTS areas (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255) UNIQUE NOT NULL,
    description TEXT DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS lead_statuses (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255) UNIQUE NOT NULL,
    color VARCHAR(255) DEFAULT '#gray',
    "order" INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS customers (
    id BIGSERIAL PRIMARY KEY,
    company VARCHAR(255) DEFAULT NULL,
    is_individual BOOLEAN DEFAULT FALSE,
    area_id BIGINT DEFAULT NULL REFERENCES areas(id) ON DELETE SET NULL,
    email VARCHAR(255) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(255) DEFAULT NULL,
    source VARCHAR(20) DEFAULT 'inbound' CHECK (source IN ('inbound', 'outbound')),
    assigned_sales_id BIGINT DEFAULT NULL REFERENCES user_profiles(id) ON DELETE SET NULL,
    lead_status_id BIGINT DEFAULT NULL REFERENCES lead_statuses(id) ON DELETE SET NULL,
    next_action_date DATE DEFAULT NULL,
    next_action_plan TEXT DEFAULT NULL,
    next_action_priority VARCHAR(10) DEFAULT NULL CHECK (next_action_priority IN ('low', 'medium', 'high')),
    next_action_status VARCHAR(10) DEFAULT 'pending' CHECK (next_action_status IN ('pending', 'done', 'overdue')),
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_customers_sales_status ON customers(assigned_sales_id, lead_status_id);
CREATE INDEX IF NOT EXISTS idx_customers_area ON customers(area_id);
CREATE INDEX IF NOT EXISTS idx_customers_next_action_date ON customers(next_action_date);

CREATE TABLE IF NOT EXISTS contacts (
    id BIGSERIAL PRIMARY KEY,
    customer_id BIGINT NOT NULL REFERENCES customers(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(255) DEFAULT NULL,
    whatsapp VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_contacts_customer_primary ON contacts(customer_id, is_primary);

CREATE TABLE IF NOT EXISTS interactions (
    id BIGSERIAL PRIMARY KEY,
    customer_id BIGINT NOT NULL REFERENCES customers(id) ON DELETE CASCADE,
    interaction_type VARCHAR(20) NOT NULL CHECK (interaction_type IN ('email_inbound', 'email_outbound', 'manual_channel', 'note')),
    channel VARCHAR(20) DEFAULT NULL CHECK (channel IN ('email', 'whatsapp', 'telephone', 'instagram', 'tiktok', 'tokopedia', 'shopee', 'lazada', 'website_chat', 'other')),
    subject VARCHAR(255) DEFAULT NULL,
    content TEXT DEFAULT NULL,
    summary TEXT DEFAULT NULL,
    created_by_type VARCHAR(10) DEFAULT 'user' CHECK (created_by_type IN ('user', 'system')),
    created_by_user_id BIGINT DEFAULT NULL REFERENCES user_profiles(id) ON DELETE SET NULL,
    lead_status_snapshot_id BIGINT DEFAULT NULL REFERENCES lead_statuses(id) ON DELETE SET NULL,
    metadata JSONB DEFAULT NULL,
    interaction_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_interactions_customer_type ON interactions(customer_id, interaction_type);
CREATE INDEX IF NOT EXISTS idx_interactions_at ON interactions(interaction_at);
CREATE INDEX IF NOT EXISTS idx_interactions_channel ON interactions(channel);

CREATE TABLE IF NOT EXISTS email_accounts (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    provider VARCHAR(255) NOT NULL,
    imap_host TEXT NOT NULL,
    imap_port INTEGER NOT NULL,
    smtp_host TEXT NOT NULL,
    smtp_port INTEGER NOT NULL,
    username TEXT NOT NULL,
    password TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_sync_at TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS emails (
    id BIGSERIAL PRIMARY KEY,
    email_account_id BIGINT NOT NULL REFERENCES email_accounts(id) ON DELETE CASCADE,
    customer_id BIGINT DEFAULT NULL REFERENCES customers(id) ON DELETE SET NULL,
    message_id VARCHAR(255) UNIQUE NOT NULL,
    from_email VARCHAR(255) NOT NULL,
    from_name VARCHAR(255) DEFAULT NULL,
    to_emails TEXT NOT NULL,
    cc_emails TEXT DEFAULT NULL,
    bcc_emails TEXT DEFAULT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    body_text TEXT DEFAULT NULL,
    body_html TEXT DEFAULT NULL,
    is_inbound BOOLEAN DEFAULT TRUE,
    is_processed BOOLEAN DEFAULT FALSE,
    raw_headers JSONB DEFAULT NULL,
    raw_body TEXT DEFAULT NULL,
    email_date TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_emails_account_processed ON emails(email_account_id, is_processed);
CREATE INDEX IF NOT EXISTS idx_emails_customer_id ON emails(customer_id);
CREATE INDEX IF NOT EXISTS idx_emails_date ON emails(email_date);

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT DEFAULT NULL REFERENCES user_profiles(id) ON DELETE SET NULL,
    customer_id BIGINT DEFAULT NULL REFERENCES customers(id) ON DELETE CASCADE,
    action VARCHAR(255) NOT NULL,
    model_type VARCHAR(255) DEFAULT NULL,
    model_id BIGINT DEFAULT NULL,
    old_values JSONB DEFAULT NULL,
    new_values JSONB DEFAULT NULL,
    ip_address VARCHAR(255) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_audit_customer_action ON audit_logs(customer_id, action);
CREATE INDEX IF NOT EXISTS idx_audit_model_type ON audit_logs(model_type);
CREATE INDEX IF NOT EXISTS idx_audit_created_at ON audit_logs(created_at);

CREATE TABLE IF NOT EXISTS invoices (
    id BIGSERIAL PRIMARY KEY,
    customer_id BIGINT NOT NULL REFERENCES customers(id) ON DELETE CASCADE,
    invoice_number VARCHAR(255) UNIQUE NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE DEFAULT NULL,
    subtotal NUMERIC(15, 2) DEFAULT 0.00,
    tax NUMERIC(15, 2) DEFAULT 0.00,
    discount NUMERIC(15, 2) DEFAULT 0.00,
    total NUMERIC(15, 2) DEFAULT 0.00,
    status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft', 'sent', 'paid', 'cancelled')),
    notes TEXT DEFAULT NULL,
    created_by BIGINT DEFAULT NULL REFERENCES user_profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS invoice_items (
    id BIGSERIAL PRIMARY KEY,
    invoice_id BIGINT NOT NULL REFERENCES invoices(id) ON DELETE CASCADE,
    item_name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    quantity INTEGER DEFAULT 1,
    unit_price NUMERIC(15, 2) DEFAULT 0.00,
    total_price NUMERIC(15, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS email_settings (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT DEFAULT NULL REFERENCES user_profiles(id) ON DELETE CASCADE,
    mail_host VARCHAR(255) DEFAULT NULL,
    mail_port INTEGER DEFAULT NULL,
    mail_username VARCHAR(255) DEFAULT NULL,
    mail_password VARCHAR(255) DEFAULT NULL,
    mail_encryption VARCHAR(255) DEFAULT NULL,
    mail_from_address VARCHAR(255) DEFAULT NULL,
    mail_from_name VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS broadcast_email_history (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES user_profiles(id) ON DELETE CASCADE,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    filter_type VARCHAR(255) NOT NULL,
    area_id BIGINT DEFAULT NULL REFERENCES areas(id) ON DELETE SET NULL,
    recipient_count INTEGER DEFAULT 0,
    recipients JSONB NOT NULL,
    has_attachments BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS broadcast_email_drafts (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES user_profiles(id) ON DELETE CASCADE,
    subject VARCHAR(255) DEFAULT NULL,
    body TEXT DEFAULT NULL,
    filter_type VARCHAR(255) DEFAULT NULL,
    area_id BIGINT DEFAULT NULL REFERENCES areas(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- SEED DATA TENANT DATABASE
INSERT INTO user_profiles (id, master_user_id, name, email, role, is_active, created_at, updated_at)
VALUES 
(1, 1, 'Admin System', 'admin@flowcrm.test', 'admin', TRUE, NOW(), NOW()),
(2, 2, 'Budi Santoso', 'sales1@flowcrm.test', 'sales', TRUE, NOW(), NOW()),
(3, 3, 'Siti Rahmawati', 'sales2@flowcrm.test', 'sales', TRUE, NOW(), NOW()),
(4, 4, 'Andi Marketing', 'marketing@flowcrm.test', 'marketing', TRUE, NOW(), NOW()),
(5, 5, 'Manager Utama', 'manager@flowcrm.test', 'manager', TRUE, NOW(), NOW())
ON CONFLICT (id) DO NOTHING;

INSERT INTO areas (id, name, code, description, is_active, created_at, updated_at)
VALUES
(1, 'Jakarta', 'JKT', 'Area Jakarta dan sekitarnya', TRUE, NOW(), NOW()),
(2, 'Bandung', 'BDG', 'Area Bandung dan Jawa Barat', TRUE, NOW(), NOW()),
(3, 'Surabaya', 'SBY', 'Area Surabaya dan Jawa Timur', TRUE, NOW(), NOW()),
(4, 'Medan', 'MDN', 'Area Medan dan Sumatera', TRUE, NOW(), NOW()),
(5, 'Bali', 'DPS', 'Area Bali dan Nusa Tenggara', TRUE, NOW(), NOW()),
(6, 'Makassar', 'MKS', 'Area Makassar dan Sulawesi', TRUE, NOW(), NOW())
ON CONFLICT (id) DO NOTHING;

INSERT INTO lead_statuses (id, name, code, color, "order", is_active, created_at, updated_at)
VALUES
(1, 'New Lead', 'new', '#A78BFA', 1, TRUE, NOW(), NOW()),
(2, 'Contacted', 'contacted', '#60A5FA', 2, TRUE, NOW(), NOW()),
(3, 'Qualified', 'qualified', '#FBBF24', 3, TRUE, NOW(), NOW()),
(4, 'Won', 'won', '#34D399', 4, TRUE, NOW(), NOW()),
(5, 'Cold Lead', 'cold', '#93C5FD', 5, TRUE, NOW(), NOW()),
(6, 'Warm Lead', 'warm', '#FCD34D', 6, TRUE, NOW(), NOW()),
(7, 'Hot Lead', 'hot', '#F87171', 7, TRUE, NOW(), NOW()),
(8, 'Dormant Lead', 'dormant', '#9CA3AF', 8, TRUE, NOW(), NOW()),
(9, 'Lost Lead', 'lost', '#6B7280', 9, TRUE, NOW(), NOW())
ON CONFLICT (id) DO NOTHING;

SELECT setval('user_profiles_id_seq', (SELECT MAX(id) FROM user_profiles));
SELECT setval('areas_id_seq', (SELECT MAX(id) FROM areas));
SELECT setval('lead_statuses_id_seq', (SELECT MAX(id) FROM lead_statuses));
