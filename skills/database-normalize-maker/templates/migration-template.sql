-- Migration: 001_create_users_schema
-- Up: Create initial tables
-- Down: Drop all tables

-- ============================================================
-- UP MIGRATION
-- ============================================================

-- Create roles (reference table first)
CREATE TABLE IF NOT EXISTS roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_role_name (role_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create permissions (reference table)
CREATE TABLE IF NOT EXISTS permissions (
    permission_id INT PRIMARY KEY AUTO_INCREMENT,
    permission_name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create users (main table)
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_by INT,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (role_id) REFERENCES roles(role_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id),
    FOREIGN KEY (deleted_by) REFERENCES users(user_id),

    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role_id (role_id),
    INDEX idx_created_at (created_at),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create role_permissions (N:M mapping)
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(permission_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create user_audit (immutable audit trail)
CREATE TABLE IF NOT EXISTS user_audit (
    audit_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    before_state JSON,
    after_state JSON,
    changed_by INT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    correlation_id VARCHAR(36),

    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (changed_by) REFERENCES users(user_id),

    INDEX (user_id, changed_at),
    INDEX (changed_by, changed_at),
    INDEX (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create schema_version (track migrations)
CREATE TABLE IF NOT EXISTS schema_version (
    version_number INT PRIMARY KEY,
    description VARCHAR(255),
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hash VARCHAR(64)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Record this migration
INSERT INTO schema_version (version_number, description)
VALUES (1, 'Initial schema: users, roles, permissions, audit');

-- ============================================================
-- DOWN MIGRATION (Rollback)
-- ============================================================

-- DROP TABLE IF EXISTS schema_version;
-- DROP TABLE IF EXISTS user_audit;
-- DROP TABLE IF EXISTS role_permissions;
-- DROP TABLE IF EXISTS users;
-- DROP TABLE IF EXISTS roles;
-- DROP TABLE IF EXISTS permissions;

