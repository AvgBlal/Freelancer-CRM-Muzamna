-- =============================================================================
-- FCM-Native: Complete Database Schema + Demo Data
-- Freelancer Client Manager - نظام إدارة العملاء للمستقلين
-- =============================================================================
-- Run via install wizard or: mysql -u root -p your_database < install/database.sql
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '+02:00';

-- =============================================================================
-- TABLES
-- =============================================================================

-- Users (includes role/department columns from migration 005)
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'employee') NOT NULL DEFAULT 'admin',
    department VARCHAR(100) NULL,
    max_tasks_capacity INT DEFAULT 5,
    avatar VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clients
CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    type ENUM('individual','company') NOT NULL DEFAULT 'individual',
    phone VARCHAR(50) NULL,
    email VARCHAR(190) NULL,
    website VARCHAR(255) NULL,
    country VARCHAR(100) NULL,
    city VARCHAR(100) NULL,
    timezone VARCHAR(64) NULL,
    preferred_channel ENUM('whatsapp','email','phone') NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Client Contacts
CREATE TABLE client_contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(190) NOT NULL,
    job_title VARCHAR(190) NULL,
    email VARCHAR(190) NULL,
    phone VARCHAR(50) NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tags
CREATE TABLE tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Client-Tag Links
CREATE TABLE client_tags (
    client_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (client_id, tag_id),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services (includes is_personal from migration 010)
CREATE TABLE services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    status ENUM('active','expired','paused','cancelled') NOT NULL DEFAULT 'active',
    start_date DATE NULL,
    end_date DATE NOT NULL,
    auto_renew TINYINT(1) NOT NULL DEFAULT 0,
    price_amount DECIMAL(12,2) NULL,
    currency_code VARCHAR(10) NULL,
    currency_custom VARCHAR(10) NULL,
    billing_cycle VARCHAR(20) NULL,
    notes_sensitive TEXT NULL,
    is_personal TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service-Client Links
CREATE TABLE service_clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_service_client (service_id, client_id),
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service Renewals
CREATE TABLE service_renewals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id BIGINT UNSIGNED NOT NULL,
    old_end_date DATE NOT NULL,
    new_end_date DATE NOT NULL,
    renewed_by BIGINT UNSIGNED DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (renewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects
CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status ENUM('idea','in_progress','paused','completed','cancelled') NOT NULL DEFAULT 'idea',
    priority ENUM('low','normal','high') NOT NULL DEFAULT 'normal',
    start_date DATE NULL,
    due_date DATE NULL,
    progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Project TODOs
CREATE TABLE project_todos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    state ENUM('todo','doing','done') NOT NULL DEFAULT 'todo',
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Recurrence Patterns (must be created before tasks table)
CREATE TABLE task_recurrence (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pattern ENUM('daily', 'weekly', 'monthly') NOT NULL,
    interval_value INT DEFAULT 1,
    days_of_week JSON NULL,
    day_of_month INT NULL,
    end_date DATE NULL,
    max_occurrences INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tasks
CREATE TABLE tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status ENUM('draft', 'assigned', 'in_progress', 'in_review', 'revision_needed', 'completed', 'on_hold', 'blocked', 'cancelled') DEFAULT 'draft',
    priority ENUM('urgent', 'high', 'normal', 'low') DEFAULT 'normal',
    created_by BIGINT UNSIGNED NOT NULL,
    assigned_to BIGINT UNSIGNED NULL,
    client_id BIGINT UNSIGNED NULL,
    project_id BIGINT UNSIGNED NULL,
    service_id BIGINT UNSIGNED NULL,
    recurrence_id BIGINT UNSIGNED NULL,
    start_date DATE NULL,
    due_date DATE NOT NULL,
    completed_at DATETIME NULL,
    estimated_hours DECIMAL(5,2) NULL,
    actual_hours DECIMAL(5,2) DEFAULT 0,
    progress_pct INT DEFAULT 0 CHECK (progress_pct BETWEEN 0 AND 100),
    is_recurring BOOLEAN DEFAULT FALSE,
    parent_task_id BIGINT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (recurrence_id) REFERENCES task_recurrence(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_task_id) REFERENCES tasks(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Comments
CREATE TABLE task_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    mentions JSON NULL,
    is_system_generated BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Attachments
CREATE TABLE task_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Dependencies
CREATE TABLE task_dependencies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    depends_on_task_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (depends_on_task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    UNIQUE KEY unique_dependency (task_id, depends_on_task_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Templates
CREATE TABLE task_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_by BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    default_hours DECIMAL(5,2) NULL,
    priority ENUM('urgent', 'high', 'normal', 'low') DEFAULT 'normal',
    tags_json JSON NULL,
    category VARCHAR(100) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task Time Logs
CREATE TABLE task_time_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    log_date DATE NOT NULL,
    hours DECIMAL(4,2) NOT NULL CHECK (hours > 0 AND hours <= 24),
    description VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dues (money owed TO freelancer)
CREATE TABLE dues (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_name VARCHAR(255) NOT NULL,
    person_phone VARCHAR(50) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    currency_code VARCHAR(10) NOT NULL DEFAULT 'EGP',
    due_date DATE DEFAULT NULL,
    status ENUM('pending', 'partial', 'paid', 'cancelled') NOT NULL DEFAULT 'pending',
    paid_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    paid_at DATETIME DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Expenses (money freelancer owes)
CREATE TABLE expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category ENUM('hosting', 'software', 'domains', 'tools', 'subscriptions', 'freelancer', 'office', 'marketing', 'taxes', 'other') NOT NULL DEFAULT 'other',
    amount DECIMAL(12, 2) NOT NULL,
    currency_code VARCHAR(10) NOT NULL DEFAULT 'EGP',
    due_date DATE DEFAULT NULL,
    status ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending',
    paid_at DATETIME DEFAULT NULL,
    vendor VARCHAR(255) DEFAULT NULL,
    is_recurring TINYINT(1) NOT NULL DEFAULT 0,
    billing_cycle ENUM('monthly', 'yearly', 'one_time') DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notes (personal notes & reminders)
CREATE TABLE notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT DEFAULT NULL,
    category ENUM('general', 'idea', 'reminder', 'financial', 'personal') DEFAULT 'general',
    priority ENUM('low', 'normal', 'high') DEFAULT 'normal',
    status ENUM('active', 'archived') DEFAULT 'active',
    is_pinned TINYINT(1) DEFAULT 0,
    due_date DATE DEFAULT NULL,
    color VARCHAR(7) DEFAULT NULL,
    created_by BIGINT UNSIGNED DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs (audit trail)
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    user_name VARCHAR(100) DEFAULT NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id BIGINT UNSIGNED DEFAULT NULL,
    entity_title VARCHAR(255) DEFAULT NULL,
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings (key-value config)
CREATE TABLE settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cron Runs
CREATE TABLE cron_runs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    run_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('success','fail') NOT NULL,
    summary TEXT NULL,
    error_message TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notification Logs
CREATE TABLE notification_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    channel ENUM('email','whatsapp') NOT NULL,
    target VARCHAR(255) NULL,
    payload_summary TEXT NULL,
    status ENUM('sent','fail') NOT NULL,
    sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    error_message TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Unpaid Tasks (emergency/unquoted work per client)
CREATE TABLE unpaid_tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    hours DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    total_cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    currency_code VARCHAR(10) NOT NULL DEFAULT 'EGP',
    assigned_to BIGINT UNSIGNED DEFAULT NULL,
    attachment VARCHAR(500) DEFAULT NULL,
    status ENUM('pending','quoted','invoiced','paid','cancelled') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Safe Items (digital vault with quotation/invoice types)
CREATE TABLE safe_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('general','quotation','invoice') NOT NULL DEFAULT 'general',
    client_id BIGINT UNSIGNED DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    url VARCHAR(2048) DEFAULT NULL,
    file_path VARCHAR(500) DEFAULT NULL,
    file_original_name VARCHAR(255) DEFAULT NULL,
    file_size INT UNSIGNED DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    tags TEXT DEFAULT NULL,
    created_by BIGINT UNSIGNED DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Safe Item Files (multi-file support)
CREATE TABLE safe_item_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    safe_item_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_original_name VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (safe_item_id) REFERENCES safe_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service Types (dynamic, replaces hardcoded list)
CREATE TABLE service_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    label VARCHAR(100) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- INDEXES
-- =============================================================================

-- Users
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_active ON users(is_active);
CREATE INDEX idx_users_department ON users(department);

-- Clients
CREATE INDEX idx_clients_type ON clients(type);
CREATE INDEX idx_clients_created ON clients(created_at);
CREATE INDEX idx_clients_name ON clients(name);

-- Services
CREATE INDEX idx_services_status ON services(status);
CREATE INDEX idx_services_end_date ON services(end_date);
CREATE INDEX idx_services_status_end ON services(status, end_date);
CREATE INDEX idx_services_is_personal ON services(is_personal);

-- Service Renewals
CREATE INDEX idx_renewals_service ON service_renewals(service_id);

-- Projects
CREATE INDEX idx_projects_status ON projects(status);
CREATE INDEX idx_projects_due_date ON projects(due_date);
CREATE INDEX idx_projects_client ON projects(client_id);
CREATE INDEX idx_projects_status_due ON projects(status, due_date);

-- Foreign key lookups
CREATE INDEX idx_client_contacts_client ON client_contacts(client_id);
CREATE INDEX idx_project_todos_project ON project_todos(project_id);
CREATE INDEX idx_service_clients_service ON service_clients(service_id);
CREATE INDEX idx_service_clients_client ON service_clients(client_id);

-- Tasks
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to);
CREATE INDEX idx_tasks_due_date ON tasks(due_date);
CREATE INDEX idx_tasks_client_id ON tasks(client_id);
CREATE INDEX idx_tasks_project_id ON tasks(project_id);
CREATE INDEX idx_tasks_created_by ON tasks(created_by);
CREATE INDEX idx_tasks_priority ON tasks(priority);
CREATE INDEX idx_tasks_status_due ON tasks(status, due_date);
CREATE INDEX idx_tasks_assigned_status ON tasks(assigned_to, status);

-- Task supporting tables
CREATE INDEX idx_task_comments_task ON task_comments(task_id);
CREATE INDEX idx_task_comments_user ON task_comments(user_id);
CREATE INDEX idx_task_comments_created ON task_comments(created_at);
CREATE INDEX idx_task_attachments_task ON task_attachments(task_id);
CREATE INDEX idx_task_deps_task ON task_dependencies(task_id);
CREATE INDEX idx_task_deps_depends ON task_dependencies(depends_on_task_id);
CREATE INDEX idx_task_recurrence_pattern ON task_recurrence(pattern);
CREATE INDEX idx_task_templates_category ON task_templates(category);
CREATE INDEX idx_task_templates_created_by ON task_templates(created_by);
CREATE INDEX idx_task_time_task ON task_time_logs(task_id);
CREATE INDEX idx_task_time_user ON task_time_logs(user_id);
CREATE INDEX idx_task_time_date ON task_time_logs(log_date);

-- Dues
CREATE INDEX idx_dues_status ON dues(status);
CREATE INDEX idx_dues_due_date ON dues(due_date);
CREATE INDEX idx_dues_person ON dues(person_name);

-- Expenses
CREATE INDEX idx_expenses_status ON expenses(status);
CREATE INDEX idx_expenses_category ON expenses(category);
CREATE INDEX idx_expenses_due_date ON expenses(due_date);

-- Notes
CREATE INDEX idx_notes_status ON notes(status);
CREATE INDEX idx_notes_pinned ON notes(is_pinned);
CREATE INDEX idx_notes_due_date ON notes(due_date);
CREATE INDEX idx_notes_category ON notes(category);

-- Activity Logs
CREATE INDEX idx_activity_user ON activity_logs(user_id);
CREATE INDEX idx_activity_entity ON activity_logs(entity_type, entity_id);
CREATE INDEX idx_activity_action ON activity_logs(action);
CREATE INDEX idx_activity_date ON activity_logs(created_at);

-- Cron & Notifications
CREATE INDEX idx_cron_runs_at ON cron_runs(run_at);
CREATE INDEX idx_notification_sent ON notification_logs(sent_at);

-- Unpaid Tasks
CREATE INDEX idx_unpaid_client ON unpaid_tasks(client_id);
CREATE INDEX idx_unpaid_status ON unpaid_tasks(status);

-- Safe Items
CREATE FULLTEXT INDEX idx_safe_search ON safe_items(title, notes, tags);
CREATE INDEX idx_safe_items_type ON safe_items(type);
CREATE INDEX idx_safe_items_client ON safe_items(client_id);

-- =============================================================================
-- DEFAULT SETTINGS
-- =============================================================================

INSERT INTO settings (`key`, `value`) VALUES
('default_currency', 'EGP'),
('reminder_days', '30'),
('email_enabled', '0'),
('email_to', 'you@example.com'),
('wa_enabled', '0'),
('wa_api_url', 'https://api.whatspie.com/messages/send-text'),
('wa_token', ''),
('wa_device_id', ''),
('wa_recipients', '');

-- Default service types
INSERT INTO service_types (slug, label, sort_order) VALUES
('hosting', 'استضافة', 1),
('vps', 'VPS', 2),
('support', 'دعم فني', 3),
('domain', 'نطاق', 4),
('maintenance', 'صيانة', 5),
('email', 'بريد إلكتروني', 6),
('custom', 'مخصص', 7);

-- =============================================================================
-- DEMO DATA
-- =============================================================================
-- Login: admin@demo.com / password
-- 3 users, 6 clients, 10 services, 5 projects, 12 tasks, dues, expenses, notes
-- =============================================================================

-- Users (password = "password" bcrypt hash)
INSERT INTO users (id, name, email, password_hash, role, department, max_tasks_capacity, is_active, created_at) VALUES
(1, 'أحمد المدير', 'admin@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'الإدارة', 10, 1, '2025-01-01 09:00:00'),
(2, 'سارة المشرفة', 'sara@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'إدارة المشاريع', 8, 1, '2025-01-05 09:00:00'),
(3, 'محمد المطور', 'mohamed@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 'التطوير', 5, 1, '2025-01-10 09:00:00');

-- Tags
INSERT INTO tags (id, name) VALUES
(1, 'VIP'), (2, 'نشط'), (3, 'جديد'), (4, 'متكرر'),
(5, 'تقنية'), (6, 'تصميم'), (7, 'تسويق'), (8, 'استشارات');

-- Clients
INSERT INTO clients (id, name, type, phone, email, website, country, city, timezone, preferred_channel, notes, created_at) VALUES
(1, 'شركة التقنية المتقدمة', 'company', '01001234567', 'info@techadvanced.com', 'https://techadvanced.com', 'مصر', 'القاهرة', 'Africa/Cairo', 'email', 'عميل رئيسي - عقد سنوي للخدمات التقنية', '2025-01-15 10:00:00'),
(2, 'مؤسسة النجاح للتسويق', 'company', '01112345678', 'contact@najah-marketing.com', 'https://najah-marketing.com', 'مصر', 'الإسكندرية', 'Africa/Cairo', 'whatsapp', 'مؤسسة تسويق رقمي - مشاريع متعددة', '2025-02-01 11:00:00'),
(3, 'خالد أحمد', 'individual', '01223456789', 'khaled@gmail.com', NULL, 'مصر', 'المنصورة', 'Africa/Cairo', 'whatsapp', 'مطور مستقل يحتاج استضافة وتصميم', '2025-02-15 09:30:00'),
(4, 'شركة الإبداع للبرمجيات', 'company', '01098765432', 'hello@ibdaa-soft.com', 'https://ibdaa-soft.com', 'السعودية', 'الرياض', 'Asia/Riyadh', 'email', 'شركة سعودية - مشاريع كبيرة', '2025-03-01 08:00:00'),
(5, 'نورة السعيد', 'individual', '01567890123', 'noura@outlook.com', NULL, 'مصر', 'القاهرة', 'Africa/Cairo', 'phone', 'صاحبة متجر إلكتروني', '2025-03-10 14:00:00'),
(6, 'مجموعة الريادة التجارية', 'company', '01234567890', 'admin@riyada-group.com', 'https://riyada-group.com', 'الإمارات', 'دبي', 'Asia/Dubai', 'email', 'مجموعة شركات - عقود متعددة', '2025-04-01 10:30:00');

-- Client Contacts
INSERT INTO client_contacts (client_id, name, job_title, email, phone, notes) VALUES
(1, 'عمر حسن', 'مدير تقنية المعلومات', 'omar@techadvanced.com', '01001111111', 'جهة الاتصال الرئيسية'),
(1, 'فاطمة علي', 'مديرة المشاريع', 'fatma@techadvanced.com', '01001111112', 'تتابع المشاريع الجديدة'),
(2, 'يوسف محمد', 'مدير التسويق', 'youssef@najah-marketing.com', '01112222221', 'يتابع كل المشاريع'),
(2, 'ليلى أحمد', 'مصممة', 'layla@najah-marketing.com', '01112222222', 'التواصل لشؤون التصميم'),
(4, 'عبدالله الشمري', 'المدير التنفيذي', 'abdullah@ibdaa-soft.com', '966501234567', 'صاحب القرار'),
(4, 'منال العتيبي', 'مديرة العمليات', 'manal@ibdaa-soft.com', '966502345678', 'تتابع التسليمات'),
(5, 'نورة السعيد', 'مالكة', 'noura@outlook.com', '01567890123', NULL),
(6, 'سعد الحربي', 'مدير التطوير', 'saad@riyada-group.com', '971501234567', 'جهة الاتصال التقنية'),
(6, 'هند المطيري', 'مديرة المالية', 'hind@riyada-group.com', '971502345678', 'للفواتير والمدفوعات');

-- Client Tags
INSERT INTO client_tags (client_id, tag_id) VALUES
(1, 1), (1, 2), (1, 5),
(2, 2), (2, 7),
(3, 3), (3, 5),
(4, 1), (4, 4), (4, 5),
(5, 3), (5, 6),
(6, 1), (6, 2), (6, 8);

-- Services
INSERT INTO services (id, title, type, status, start_date, end_date, auto_renew, price_amount, currency_code, billing_cycle, notes_sensitive, is_personal, created_at) VALUES
(1, 'استضافة VPS - خادم رئيسي', 'hosting', 'active', '2025-01-01', '2026-01-01', 1, 2400.00, 'EGP', 'yearly', 'IP: 192.168.1.100 | User: root | Provider: DigitalOcean', 0, '2025-01-01 10:00:00'),
(2, 'نطاق techadvanced.com', 'domain', 'active', '2025-03-01', '2026-03-01', 1, 600.00, 'EGP', 'yearly', 'Registrar: Namecheap | EPP: xxx', 0, '2025-03-01 10:00:00'),
(3, 'تصميم وتطوير موقع التسويق', 'development', 'active', '2025-02-01', '2025-08-15', 0, 15000.00, 'EGP', 'one_time', NULL, 0, '2025-02-01 10:00:00'),
(4, 'استضافة مشتركة - متجر نورة', 'hosting', 'active', '2025-03-10', '2026-03-10', 1, 1200.00, 'EGP', 'yearly', 'cPanel login: noura_store | Provider: Hostinger', 0, '2025-03-10 10:00:00'),
(5, 'صيانة شهرية - شركة الإبداع', 'maintenance', 'active', '2025-04-01', '2025-10-01', 0, 3000.00, 'SAR', 'monthly', NULL, 0, '2025-04-01 10:00:00'),
(6, 'شهادة SSL - خادم الريادة', 'ssl', 'expired', '2024-06-01', '2025-06-01', 0, 500.00, 'EGP', 'yearly', 'Let''s Encrypt Wildcard', 0, '2024-06-01 10:00:00'),
(7, 'استضافة موقع خالد', 'hosting', 'active', '2025-02-15', '2025-08-15', 1, 800.00, 'EGP', 'yearly', NULL, 0, '2025-02-15 10:00:00'),
(8, 'اشتراك Netflix', 'subscription', 'active', '2025-01-01', '2025-12-31', 1, 200.00, 'EGP', 'monthly', NULL, 1, '2025-01-01 10:00:00'),
(9, 'اشتراك GitHub Pro', 'subscription', 'active', '2025-01-01', '2026-01-01', 1, 48.00, 'USD', 'yearly', 'username: avgblal', 1, '2025-01-01 10:00:00'),
(10, 'اشتراك Adobe Creative Cloud', 'subscription', 'active', '2025-02-01', '2026-02-01', 1, 600.00, 'USD', 'yearly', NULL, 1, '2025-02-01 10:00:00');

-- Service-Client Links
INSERT INTO service_clients (service_id, client_id) VALUES
(1, 1), (2, 1), (3, 2), (4, 5), (5, 4), (6, 6), (7, 3);

-- Service Renewals
INSERT INTO service_renewals (service_id, old_end_date, new_end_date, renewed_by, notes, created_at) VALUES
(1, '2025-01-01', '2026-01-01', 1, 'تجديد سنوي تلقائي', '2025-01-01 09:00:00'),
(2, '2025-03-01', '2026-03-01', 1, NULL, '2025-03-01 09:00:00');

-- Projects
INSERT INTO projects (id, client_id, title, description, status, priority, start_date, due_date, progress, created_at) VALUES
(1, 1, 'تطوير لوحة تحكم داخلية', 'لوحة تحكم لإدارة العمليات الداخلية مع تقارير ورسوم بيانية', 'in_progress', 'high', '2025-02-01', '2025-07-30', 65, '2025-02-01 10:00:00'),
(2, 2, 'موقع تسويقي جديد', 'تصميم وتطوير موقع تسويقي متجاوب مع لوحة إدارة محتوى', 'in_progress', 'normal', '2025-03-01', '2025-08-15', 40, '2025-03-01 10:00:00'),
(3, 4, 'تطبيق موبايل - إدارة مخزون', 'تطبيق Flutter لإدارة المخزون مع باركود سكانر', 'idea', 'high', '2025-06-01', '2025-12-01', 0, '2025-04-15 10:00:00'),
(4, 5, 'متجر إلكتروني', 'تطوير متجر إلكتروني كامل مع بوابة دفع', 'completed', 'normal', '2025-01-15', '2025-04-30', 100, '2025-01-15 10:00:00'),
(5, 6, 'نظام ERP مصغر', 'نظام إدارة موارد مصغر للمجموعة يشمل المحاسبة والموارد البشرية', 'paused', 'high', '2025-03-01', '2025-06-15', 25, '2025-03-01 10:00:00');

-- Project TODOs
INSERT INTO project_todos (project_id, title, state, sort_order) VALUES
(1, 'تصميم واجهة المستخدم (UI/UX)', 'done', 1),
(1, 'تطوير API الخلفية', 'done', 2),
(1, 'تطوير لوحة التحكم الأمامية', 'doing', 3),
(1, 'إضافة التقارير والرسوم البيانية', 'todo', 4),
(1, 'اختبار وإصلاح الأخطاء', 'todo', 5),
(2, 'تصميم الصفحة الرئيسية', 'done', 1),
(2, 'تطوير الصفحات الداخلية', 'doing', 2),
(2, 'تطوير لوحة إدارة المحتوى', 'todo', 3),
(2, 'تحسين SEO', 'todo', 4),
(4, 'تصميم واجهة المتجر', 'done', 1),
(4, 'تطوير سلة التسوق', 'done', 2),
(4, 'ربط بوابة الدفع', 'done', 3),
(4, 'اختبار شامل', 'done', 4);

-- Tasks
INSERT INTO tasks (id, title, description, status, priority, created_by, assigned_to, client_id, project_id, service_id, start_date, due_date, completed_at, estimated_hours, actual_hours, progress_pct, created_at) VALUES
(1, 'تصميم صفحة الهبوط', 'تصميم Landing Page متجاوبة مع RTL', 'completed', 'high', 1, 3, 2, 2, NULL, '2025-03-01', '2025-03-15', '2025-03-14 16:00:00', 20.00, 18.50, 100, '2025-03-01 10:00:00'),
(2, 'تطوير REST API للمخزون', 'إنشاء API endpoints لعمليات CRUD للمخزون', 'in_progress', 'urgent', 1, 3, 4, 3, NULL, '2025-06-01', '2025-07-15', NULL, 40.00, 12.00, 30, '2025-06-01 10:00:00'),
(3, 'إعداد خادم الإنتاج', 'تجهيز VPS مع Nginx, PHP 8.2, MySQL 8', 'completed', 'high', 1, 2, 1, 1, 1, '2025-02-01', '2025-02-10', '2025-02-09 14:00:00', 8.00, 6.50, 100, '2025-02-01 10:00:00'),
(4, 'تحسين أداء قاعدة البيانات', 'إضافة فهارس وتحسين الاستعلامات البطيئة', 'in_review', 'normal', 2, 3, 1, 1, NULL, '2025-05-01', '2025-05-20', NULL, 12.00, 10.00, 85, '2025-05-01 10:00:00'),
(5, 'كتابة توثيق API', 'توثيق كامل لـ API باستخدام Swagger/OpenAPI', 'assigned', 'normal', 1, 2, 4, 3, NULL, '2025-07-01', '2025-07-30', NULL, 16.00, 0.00, 0, '2025-07-01 10:00:00'),
(6, 'اختبار الدفع الإلكتروني', 'اختبار بوابة Paymob في بيئة Sandbox', 'completed', 'urgent', 1, 3, 5, 4, NULL, '2025-04-01', '2025-04-10', '2025-04-08 11:00:00', 6.00, 5.00, 100, '2025-04-01 10:00:00'),
(7, 'تصميم شعار جديد', 'تصميم هوية بصرية جديدة مع 3 مقترحات', 'on_hold', 'low', 2, NULL, 6, 5, NULL, '2025-05-01', '2025-06-01', NULL, 10.00, 0.00, 0, '2025-05-01 10:00:00'),
(8, 'ترحيل البيانات القديمة', 'نقل بيانات النظام القديم إلى النظام الجديد', 'blocked', 'high', 1, 2, 6, 5, NULL, '2025-04-15', '2025-05-15', NULL, 20.00, 4.00, 20, '2025-04-15 10:00:00'),
(9, 'تحديث شهادة SSL', 'تجديد شهادة SSL لخادم الريادة المنتهية', 'draft', 'urgent', 1, NULL, 6, NULL, 6, NULL, '2025-07-01', NULL, 2.00, 0.00, 0, '2025-06-10 10:00:00'),
(10, 'تحسين سرعة الموقع', 'ضغط الصور، تفعيل Cache، تحسين CSS/JS', 'in_progress', 'normal', 2, 3, 2, 2, NULL, '2025-06-01', '2025-06-30', NULL, 8.00, 3.00, 40, '2025-06-01 10:00:00'),
(11, 'إعداد نظام النسخ الاحتياطي', 'تجهيز نسخ احتياطي يومي تلقائي للقاعدة والملفات', 'assigned', 'high', 1, 2, 1, 1, 1, '2025-06-15', '2025-07-01', NULL, 6.00, 0.00, 0, '2025-06-15 10:00:00'),
(12, 'مراجعة أمنية شاملة', 'فحص الثغرات الأمنية وتطبيق أفضل الممارسات', 'draft', 'high', 1, NULL, NULL, NULL, NULL, NULL, '2025-08-01', NULL, 15.00, 0.00, 0, '2025-06-20 10:00:00');

-- Task Comments
INSERT INTO task_comments (task_id, user_id, message, is_system_generated, created_at) VALUES
(1, 1, 'تم إنشاء المهمة', 1, '2025-03-01 10:00:00'),
(1, 3, 'بدأت العمل على التصميم، سأستخدم Figma', 0, '2025-03-02 09:00:00'),
(1, 3, 'المقترح الأول جاهز للمراجعة', 0, '2025-03-08 16:00:00'),
(1, 2, 'التصميم ممتاز! تمت الموافقة مع تعديلات بسيطة على الألوان', 0, '2025-03-09 10:00:00'),
(1, 1, 'تغيير الحالة: مكتمل', 1, '2025-03-14 16:00:00'),
(2, 1, 'تم إنشاء المهمة', 1, '2025-06-01 10:00:00'),
(2, 1, 'تم التعيين إلى: محمد المطور', 1, '2025-06-01 10:00:00'),
(2, 3, 'بدأت بتصميم هيكل قاعدة البيانات', 0, '2025-06-03 09:00:00'),
(2, 3, 'اكتملت endpoints: GET /products, POST /products, PUT /products/:id', 0, '2025-06-15 17:00:00'),
(4, 3, 'وجدت 5 استعلامات بطيئة في جدول services', 0, '2025-05-05 11:00:00'),
(4, 3, 'تم إضافة الفهارس المطلوبة، الأداء تحسن 70%', 0, '2025-05-15 14:00:00'),
(8, 2, 'متوقف بسبب عدم توفر صلاحيات الوصول للنظام القديم', 0, '2025-04-20 09:00:00');

-- Task Templates
INSERT INTO task_templates (created_by, name, description, default_hours, priority, category, is_active) VALUES
(1, 'إعداد خادم جديد', 'تجهيز VPS: تثبيت OS، تحديثات، Nginx، PHP، MySQL، SSL، Firewall', 8.00, 'high', 'البنية التحتية', 1),
(1, 'مراجعة كود', 'مراجعة الكود المصدري: الأمان، الأداء، معايير الكتابة', 4.00, 'normal', 'تطوير', 1),
(2, 'تسليم مشروع', 'تجهيز بيئة الإنتاج، نقل الكود، اختبار نهائي، تسليم للعميل', 6.00, 'high', 'إدارة', 1);

-- Task Time Logs
INSERT INTO task_time_logs (task_id, user_id, log_date, hours, description) VALUES
(1, 3, '2025-03-02', 4.00, 'تصميم أولي في Figma'),
(1, 3, '2025-03-05', 6.00, 'تطوير HTML/CSS'),
(1, 3, '2025-03-08', 5.50, 'تعديلات وتحسينات'),
(1, 3, '2025-03-14', 3.00, 'التعديلات النهائية'),
(2, 3, '2025-06-03', 4.00, 'تصميم قاعدة البيانات'),
(2, 3, '2025-06-05', 4.00, 'تطوير CRUD endpoints'),
(2, 3, '2025-06-10', 4.00, 'إضافة Authentication'),
(3, 2, '2025-02-05', 3.50, 'تثبيت وإعداد النظام'),
(3, 2, '2025-02-08', 3.00, 'تهيئة SSL و Firewall'),
(4, 3, '2025-05-05', 3.00, 'تحليل الاستعلامات البطيئة'),
(4, 3, '2025-05-10', 4.00, 'إضافة الفهارس والتحسينات'),
(4, 3, '2025-05-15', 3.00, 'اختبار الأداء بعد التحسين'),
(6, 3, '2025-04-05', 3.00, 'إعداد بيئة Sandbox'),
(6, 3, '2025-04-08', 2.00, 'اختبار عمليات الدفع');

-- Dues
INSERT INTO dues (person_name, person_phone, description, amount, currency_code, due_date, status, paid_amount, paid_at, notes, created_at) VALUES
('عمر حسن - شركة التقنية', '01001111111', 'دفعة أولى مشروع لوحة التحكم', 10000.00, 'EGP', '2025-03-01', 'paid', 10000.00, '2025-03-05 10:00:00', 'تم التحويل بنكي', '2025-02-01 10:00:00'),
('عمر حسن - شركة التقنية', '01001111111', 'دفعة ثانية مشروع لوحة التحكم', 10000.00, 'EGP', '2025-06-01', 'pending', 0.00, NULL, 'مستحقة عند اكتمال 80%', '2025-02-01 10:00:00'),
('يوسف محمد - مؤسسة النجاح', '01112222221', 'تطوير موقع التسويق - كامل المبلغ', 15000.00, 'EGP', '2025-05-01', 'partial', 7500.00, NULL, 'تم استلام نصف المبلغ مقدماً', '2025-02-15 10:00:00'),
('عبدالله الشمري - الإبداع', '966501234567', 'صيانة شهر أبريل ومايو', 6000.00, 'SAR', '2025-06-15', 'pending', 0.00, NULL, 'فاتورتين شهريتين متأخرتين', '2025-05-01 10:00:00'),
('نورة السعيد', '01567890123', 'تطوير المتجر الإلكتروني', 8000.00, 'EGP', '2025-04-30', 'paid', 8000.00, '2025-05-02 11:00:00', 'تم السداد بعد التسليم', '2025-01-15 10:00:00'),
('سعد الحربي - الريادة', '971501234567', 'استشارة تقنية 3 ساعات', 1500.00, 'AED', '2025-07-01', 'pending', 0.00, NULL, NULL, '2025-06-15 10:00:00');

-- Expenses
INSERT INTO expenses (title, category, amount, currency_code, due_date, status, paid_at, vendor, is_recurring, billing_cycle, notes, created_at) VALUES
('خادم DigitalOcean', 'hosting', 50.00, 'USD', '2025-07-01', 'pending', NULL, 'DigitalOcean', 1, 'monthly', 'Droplet 4GB RAM', '2025-01-01 10:00:00'),
('نطاقات GoDaddy', 'domains', 350.00, 'EGP', '2025-09-01', 'pending', NULL, 'GoDaddy', 1, 'yearly', '3 نطاقات', '2025-01-01 10:00:00'),
('اشتراك JetBrains', 'software', 250.00, 'USD', '2025-10-01', 'pending', NULL, 'JetBrains', 1, 'yearly', 'PHPStorm + WebStorm', '2025-01-01 10:00:00'),
('إيجار مكتب', 'office', 3000.00, 'EGP', '2025-07-01', 'pending', NULL, 'المالك', 1, 'monthly', 'مكتب مشترك - المعادي', '2025-06-01 10:00:00'),
('فاتورة إنترنت', 'subscriptions', 450.00, 'EGP', '2025-06-15', 'paid', '2025-06-14 09:00:00', 'WE', 1, 'monthly', 'باقة 100 ميجا', '2025-06-01 10:00:00'),
('مصمم فريلانسر', 'freelancer', 2000.00, 'EGP', '2025-06-20', 'paid', '2025-06-20 14:00:00', 'أحمد المصمم', 0, NULL, 'تصميم 5 صفحات لموقع النجاح', '2025-06-01 10:00:00'),
('ضرائب ربع سنوية', 'taxes', 5000.00, 'EGP', '2025-07-15', 'pending', NULL, 'مصلحة الضرائب', 0, NULL, 'ضريبة القيمة المضافة Q2', '2025-06-01 10:00:00'),
('حملة إعلانية Google Ads', 'marketing', 3000.00, 'EGP', '2025-06-01', 'paid', '2025-06-01 10:00:00', 'Google', 0, NULL, 'حملة تسويقية لخدمات الاستضافة', '2025-05-15 10:00:00');

-- Notes
INSERT INTO notes (title, content, category, priority, status, is_pinned, due_date, color, created_by, created_at) VALUES
('تجديد رخصة البرمجيات', 'تجديد رخصة Adobe و JetBrains قبل انتهائها في أكتوبر', 'reminder', 'high', 'active', 1, '2025-09-15', '#e53935', 1, '2025-06-01 10:00:00'),
('فكرة مشروع: تطبيق إدارة فواتير', 'تطبيق SaaS لإدارة الفواتير للمستقلين العرب\n- فوترة تلقائية\n- تتبع مدفوعات\n- تقارير ضريبية\n- دعم RTL كامل', 'idea', 'normal', 'active', 0, NULL, '#1e88e5', 1, '2025-05-15 10:00:00'),
('ملاحظات اجتماع شركة الإبداع', 'تم الاتفاق على:\n1. تمديد عقد الصيانة 6 أشهر إضافية\n2. بدء مشروع التطبيق في يونيو\n3. زيادة السعر 10% مع التجديد', 'general', 'normal', 'active', 0, NULL, '#43a047', 1, '2025-04-10 10:00:00'),
('تحصيل مستحقات يونيو', 'مراجعة جميع المستحقات المتأخرة وإرسال تذكيرات:\n- شركة التقنية: 10,000 ج.م\n- مؤسسة النجاح: 7,500 ج.م\n- شركة الإبداع: 6,000 ر.س', 'financial', 'high', 'active', 1, '2025-06-30', '#ff8f00', 1, '2025-06-15 10:00:00'),
('قائمة أدوات للتعلم', 'أدوات يجب تعلمها:\n- Docker & Kubernetes\n- Laravel 11\n- Vue.js 3 Composition API\n- TailwindCSS', 'personal', 'low', 'active', 0, NULL, '#7b1fa2', 1, '2025-03-01 10:00:00'),
('تقرير الأداء الشهري - مايو', 'مشاريع مكتملة: 1\nمشاريع جارية: 3\nإيرادات: 25,500 ج.م\nمصروفات: 12,000 ج.م\nصافي ربح: 13,500 ج.م', 'financial', 'normal', 'archived', 0, NULL, '#00897b', 1, '2025-06-01 10:00:00'),
('تحديث portfolio الشخصي', 'إضافة آخر 3 مشاريع مكتملة إلى الموقع الشخصي وتحديث الأسعار', 'reminder', 'normal', 'active', 0, '2025-07-15', '#5c6bc0', 1, '2025-06-20 10:00:00'),
('اجتماع مع عميل محتمل', 'شركة الفجر للتقنية - يريدون نظام HR كامل\nالميزانية: ~50,000 ج.م\nالجدول: 4-6 أشهر', 'general', 'high', 'active', 1, '2025-07-05', '#e53935', 1, '2025-06-25 10:00:00');

-- Activity Logs
INSERT INTO activity_logs (user_id, user_name, action, entity_type, entity_id, entity_title, details, ip_address, created_at) VALUES
(1, 'أحمد المدير', 'create', 'client', 1, 'شركة التقنية المتقدمة', 'تم إنشاء عميل جديد', '192.168.1.10', '2025-01-15 10:00:00'),
(1, 'أحمد المدير', 'create', 'service', 1, 'استضافة VPS - خادم رئيسي', 'تم إنشاء خدمة جديدة', '192.168.1.10', '2025-01-15 10:30:00'),
(1, 'أحمد المدير', 'create', 'project', 1, 'تطوير لوحة تحكم داخلية', NULL, '192.168.1.10', '2025-02-01 10:00:00'),
(1, 'أحمد المدير', 'create', 'task', 1, 'تصميم صفحة الهبوط', NULL, '192.168.1.10', '2025-03-01 10:00:00'),
(3, 'محمد المطور', 'status_change', 'task', 1, 'تصميم صفحة الهبوط', 'تغيير الحالة: مكتمل', '192.168.1.12', '2025-03-14 16:00:00'),
(1, 'أحمد المدير', 'create', 'due', 1, 'عمر حسن - شركة التقنية', 'مستحق: 10,000 ج.م', '192.168.1.10', '2025-02-01 10:00:00'),
(1, 'أحمد المدير', 'mark_paid', 'due', 1, 'عمر حسن - شركة التقنية', 'تم تسديد 10,000 ج.م', '192.168.1.10', '2025-03-05 10:00:00'),
(2, 'سارة المشرفة', 'create', 'expense', 4, 'إيجار مكتب', 'مصروف: 3,000 ج.م', '192.168.1.11', '2025-06-01 10:00:00'),
(1, 'أحمد المدير', 'renew', 'service', 1, 'استضافة VPS - خادم رئيسي', 'تجديد حتى 2026-01-01', '192.168.1.10', '2025-01-01 09:00:00'),
(NULL, 'نظام', 'auto_expire', 'service', 6, 'شهادة SSL - خادم الريادة', 'انتهت الصلاحية تلقائياً', NULL, '2025-06-02 09:00:00'),
(NULL, 'نظام', 'cron_run', 'system', NULL, 'daily_check', 'خدمات منتهية: 1 | تنتهي قريباً: 2 | مستحقات متأخرة: 2', NULL, '2025-06-15 09:00:00'),
(1, 'أحمد المدير', 'create', 'note', 1, 'تجديد رخصة البرمجيات', NULL, '192.168.1.10', '2025-06-01 10:00:00'),
(1, 'أحمد المدير', 'create', 'note', 8, 'اجتماع مع عميل محتمل', NULL, '192.168.1.10', '2025-06-25 10:00:00'),
(3, 'محمد المطور', 'update', 'task', 2, 'تطوير REST API للمخزون', 'تحديث التقدم: 30%', '192.168.1.12', '2025-06-15 17:00:00'),
(1, 'أحمد المدير', 'login', 'user', 1, 'أحمد المدير', NULL, '192.168.1.10', '2025-06-25 08:30:00');

-- Notification Logs
INSERT INTO notification_logs (channel, target, payload_summary, status, sent_at, error_message) VALUES
('email', 'admin@demo.com', 'التقرير اليومي: 1 خدمة منتهية، 2 تنتهي قريباً', 'sent', '2025-06-15 09:01:00', NULL),
('whatsapp', '01001234567', 'تذكير: 3 مستحقات متأخرة بإجمالي 23,500 ج.م', 'sent', '2025-06-15 09:01:30', NULL),
('email', 'admin@demo.com', 'التقرير اليومي: لا توجد خدمات تحتاج انتباه', 'sent', '2025-06-14 09:01:00', NULL),
('whatsapp', '01001234567', 'تذكير يومي', 'fail', '2025-06-13 09:01:00', 'Whatspie API: Device offline');

-- Cron Runs
INSERT INTO cron_runs (status, summary, error_message) VALUES
('success', 'خدمات منتهية: 1 | تنتهي قريباً: 2 | مستحقات متأخرة: 2 | مصروفات متأخرة: 1 | بريد مرسل | واتساب: تم (مدة: 3ث)', NULL),
('success', 'خدمات منتهية: 0 | تنتهي قريباً: 2 | مستحقات متأخرة: 2 | مصروفات متأخرة: 0 | بريد مرسل (مدة: 2ث)', NULL),
('fail', 'خدمات منتهية: 0 | تنتهي قريباً: 2 | واتساب فشل (مدة: 5ث)', 'Whatspie API: Device offline');

-- Unpaid Tasks (emergency/unquoted work)
INSERT INTO unpaid_tasks (id, client_id, title, description, hours, total_cost, currency_code, assigned_to, attachment, status, created_at) VALUES
(1, 1, 'إصلاح عطل طارئ في الخادم', 'توقف الخادم الرئيسي فجأة بسبب امتلاء القرص - تم تنظيف الملفات المؤقتة وإعادة التشغيل', 3.00, 500.00, 'EGP', 3, NULL, 'pending', '2025-06-01 14:30:00'),
(2, 2, 'تعديل عاجل على تصميم الصفحة الرئيسية', 'طلب العميل تعديل الألوان والخطوط قبل العرض التقديمي صباح الغد', 2.00, 400.00, 'EGP', 3, NULL, 'quoted', '2025-06-05 20:00:00'),
(3, 4, 'ترحيل قاعدة بيانات من MySQL إلى PostgreSQL', 'طلب طارئ لترحيل البيانات بعد قرار إداري مفاجئ بتغيير قاعدة البيانات', 8.00, 4000.00, 'SAR', 2, NULL, 'pending', '2025-06-10 09:00:00'),
(4, 5, 'تحديث محتوى صفحة المنتجات', 'إضافة 15 منتج جديد مع الصور والأسعار بشكل عاجل قبل العرض الموسمي', 1.50, 300.00, 'EGP', 3, NULL, 'invoiced', '2025-05-20 11:00:00'),
(5, 6, 'تطبيق تحديث أمني طارئ', 'ثغرة أمنية حرجة في إطار العمل - تحتاج تحديث فوري وفحص شامل', 4.00, 2000.00, 'AED', 2, NULL, 'paid', '2025-05-10 08:00:00'),
(6, 1, 'إعداد بيئة اختبار جديدة', 'تجهيز staging server مطابق لبيئة الإنتاج لاختبار التحديثات الجديدة', 5.00, 800.00, 'EGP', 3, NULL, 'pending', '2025-06-18 10:00:00');

-- Safe Items - General (المخزن الآمن - عام)
INSERT INTO safe_items (id, type, client_id, title, url, file_path, file_original_name, file_size, notes, tags, created_by, created_at) VALUES
(1, 'general', NULL, 'بيانات دخول خوادم الإنتاج', NULL, NULL, NULL, NULL, 'VPS الرئيسي:\nHost: 192.168.1.100\nUser: root\nPort: 22\n\nقاعدة البيانات:\nHost: localhost\nUser: db_admin\nDB: production_db', 'خوادم,بيانات دخول,إنتاج', 1, '2025-01-20 10:00:00'),
(2, 'general', NULL, 'قالب العقد الموحد', 'https://docs.google.com/document/d/example-contract-template', NULL, NULL, NULL, 'قالب العقد المعتمد لجميع المشاريع الجديدة\nيشمل: شروط الدفع، حقوق الملكية، ضمان ما بعد التسليم\nآخر تحديث: يناير 2025', 'عقود,قانوني,قوالب', 1, '2025-01-25 10:00:00'),
(3, 'general', 1, 'وثائق API شركة التقنية المتقدمة', 'https://api.techadvanced.com/docs', NULL, NULL, NULL, 'توثيق API الداخلي للعميل\nBase URL: https://api.techadvanced.com/v2\nAuth: Bearer Token\nRate Limit: 1000 req/min', 'API,توثيق,تقنية', 1, '2025-02-10 10:00:00'),
(4, 'general', NULL, 'ملاحظات تدريب الفريق - PHP 8.2', NULL, NULL, NULL, NULL, 'محاور التدريب:\n1. Enums و Match expressions\n2. Fibers و Async\n3. Readonly properties\n4. Named arguments\n5. أفضل ممارسات الأمان\n\nالمدة: 3 ساعات\nالحضور: 5 أشخاص', 'تدريب,PHP,فريق', 1, '2025-03-15 10:00:00'),
(5, 'general', 6, 'معلومات حساب AWS - مجموعة الريادة', NULL, NULL, NULL, NULL, 'Account ID: 123456789012\nRegion: me-south-1 (البحرين)\nIAM User: riyada-admin\n\nServices:\n- EC2: 2 instances (t3.medium)\n- RDS: MySQL 8.0\n- S3: 3 buckets\n- CloudFront: 1 distribution', 'AWS,سحابي,بنية تحتية', 1, '2025-04-05 10:00:00');

-- Safe Items - Quotations (عروض الأسعار)
INSERT INTO safe_items (id, type, client_id, title, url, file_path, file_original_name, file_size, notes, tags, created_by, created_at) VALUES
(6, 'quotation', 4, 'عرض سعر - تطبيق إدارة مخزون', NULL, NULL, NULL, NULL, 'عرض سعر لتطوير تطبيق Flutter لإدارة المخزون\n\nالمكونات:\n- تطبيق موبايل (iOS + Android): 25,000 ر.س\n- لوحة تحكم ويب: 15,000 ر.س\n- API Backend: 10,000 ر.س\n- باركود سكانر: 5,000 ر.س\n\nالإجمالي: 55,000 ر.س\nالمدة: 4-6 أشهر\nالضمان: 3 أشهر بعد التسليم', 'تطبيق,موبايل,مخزون', 1, '2025-04-20 10:00:00'),
(7, 'quotation', 2, 'عرض سعر - إعادة تصميم الموقع', NULL, NULL, NULL, NULL, 'عرض سعر لإعادة تصميم موقع مؤسسة النجاح\n\nالخدمات:\n- تصميم UI/UX جديد: 5,000 ج.م\n- تطوير Frontend (React): 8,000 ج.م\n- تكامل مع CMS: 3,000 ج.م\n- تحسين SEO: 2,000 ج.م\n\nالإجمالي: 18,000 ج.م\nالمدة: 6-8 أسابيع', 'تصميم,ويب,إعادة تصميم', 1, '2025-05-01 10:00:00'),
(8, 'quotation', 3, 'عرض سعر - باقة استضافة سنوية', NULL, NULL, NULL, NULL, 'باقة استضافة شاملة لخالد أحمد\n\nالتفاصيل:\n- استضافة VPS (4GB RAM, 80GB SSD): 1,800 ج.م/سنة\n- شهادة SSL: مجاني\n- نسخ احتياطي يومي: 600 ج.م/سنة\n- دعم فني (ساعات العمل): 1,200 ج.م/سنة\n\nالإجمالي: 3,600 ج.م/سنة\nخصم 10% عند الدفع مقدماً: 3,240 ج.م', 'استضافة,VPS,سنوي', 1, '2025-05-10 10:00:00'),
(9, 'quotation', 6, 'عرض سعر - نظام ERP مصغر', NULL, NULL, NULL, NULL, 'عرض سعر لنظام إدارة موارد المجموعة\n\nالوحدات:\n- إدارة الموارد البشرية: 20,000 د.إ\n- المحاسبة والفواتير: 25,000 د.إ\n- إدارة المشاريع: 15,000 د.إ\n- التقارير والإحصائيات: 10,000 د.إ\n- تدريب الموظفين: 5,000 د.إ\n\nالإجمالي: 75,000 د.إ\nالمدة: 8-10 أشهر', 'ERP,نظام,إدارة', 1, '2025-03-10 10:00:00');

-- Safe Items - Invoices (الفواتير)
INSERT INTO safe_items (id, type, client_id, title, url, file_path, file_original_name, file_size, notes, tags, created_by, created_at) VALUES
(10, 'invoice', 5, 'فاتورة #INV-2025-001 - تطوير المتجر الإلكتروني', NULL, NULL, NULL, NULL, 'فاتورة نهائية لمشروع المتجر الإلكتروني\n\nالبنود:\n- تصميم واجهة المتجر: 2,500 ج.م\n- تطوير Backend + API: 3,000 ج.م\n- ربط بوابة الدفع (Paymob): 1,500 ج.م\n- اختبار شامل وإطلاق: 1,000 ج.م\n\nالإجمالي: 8,000 ج.م\nالحالة: مدفوعة\nتاريخ السداد: 2025-05-02', 'فاتورة,متجر,مدفوعة', 1, '2025-04-30 10:00:00'),
(11, 'invoice', 4, 'فاتورة #INV-2025-002 - صيانة شهر أبريل', NULL, NULL, NULL, NULL, 'فاتورة الصيانة الشهرية - أبريل 2025\n\nالخدمات المقدمة:\n- مراجعة أمنية: 1,000 ر.س\n- تحديثات النظام: 800 ر.س\n- إصلاح 3 أخطاء برمجية: 700 ر.س\n- نسخ احتياطي ومراقبة: 500 ر.س\n\nالإجمالي: 3,000 ر.س\nالحالة: بانتظار السداد\nتاريخ الاستحقاق: 2025-05-15', 'فاتورة,صيانة,شهري', 1, '2025-05-01 10:00:00'),
(12, 'invoice', 1, 'فاتورة #INV-2025-003 - استضافة وخدمات Q1', NULL, NULL, NULL, NULL, 'فاتورة ربع سنوية للخدمات المقدمة\n\nالبنود:\n- استضافة VPS (يناير-مارس): 600 ج.م\n- نطاق techadvanced.com: 600 ج.م\n- دعم فني (15 ساعة): 1,500 ج.م\n\nالإجمالي: 2,700 ج.م\nالحالة: مدفوعة\nتاريخ السداد: 2025-03-28', 'فاتورة,استضافة,ربع سنوي', 1, '2025-03-25 10:00:00'),
(13, 'invoice', 4, 'فاتورة #INV-2025-004 - صيانة شهر مايو', NULL, NULL, NULL, NULL, 'فاتورة الصيانة الشهرية - مايو 2025\n\nالخدمات المقدمة:\n- تحسين أداء قاعدة البيانات: 1,200 ر.س\n- تحديثات أمنية: 800 ر.س\n- إضافة ميزة التقارير: 1,000 ر.س\n\nالإجمالي: 3,000 ر.س\nالحالة: بانتظار السداد\nتاريخ الاستحقاق: 2025-06-15', 'فاتورة,صيانة,شهري', 1, '2025-06-01 10:00:00'),
(14, 'invoice', 6, 'فاتورة #INV-2025-005 - استشارة تقنية', NULL, NULL, NULL, NULL, 'فاتورة استشارة تقنية لمجموعة الريادة\n\nالتفاصيل:\n- تحليل البنية التحتية الحالية: 3 ساعات × 500 د.إ\n- تقرير التوصيات والخطة: مشمول\n\nالإجمالي: 1,500 د.إ\nالحالة: بانتظار السداد\nتاريخ الاستحقاق: 2025-07-01', 'فاتورة,استشارة,تقنية', 1, '2025-06-15 10:00:00');

-- =============================================================================
-- DONE! Login: admin@demo.com / password
-- =============================================================================
