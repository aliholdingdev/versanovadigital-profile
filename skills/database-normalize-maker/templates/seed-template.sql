-- Seed Data: Initial test data for development & testing
-- Load with: mysql -u root database < seed.sql

-- ============================================================
-- INSERT ROLES
-- ============================================================

INSERT INTO roles (role_id, role_name, description) VALUES
(1, 'admin', 'Administrator with full access'),
(2, 'user', 'Regular user with standard permissions'),
(3, 'guest', 'Guest user with read-only access'),
(4, 'moderator', 'Content moderator'),
(5, 'viewer', 'View-only access');

-- ============================================================
-- INSERT PERMISSIONS
-- ============================================================

INSERT INTO permissions (permission_id, permission_name, description) VALUES
(1, 'read', 'Read data'),
(2, 'write', 'Create and modify data'),
(3, 'delete', 'Delete data'),
(4, 'admin', 'Full admin access'),
(5, 'manage_users', 'Manage user accounts'),
(6, 'manage_roles', 'Manage roles and permissions'),
(7, 'audit', 'View audit logs'),
(8, 'export', 'Export data');

-- ============================================================
-- INSERT ROLE PERMISSIONS
-- ============================================================

-- Admin: all permissions
INSERT INTO role_permissions (role_id, permission_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8);

-- User: read, write
INSERT INTO role_permissions (role_id, permission_id) VALUES
(2, 1), (2, 2);

-- Guest: read only
INSERT INTO role_permissions (role_id, permission_id) VALUES
(3, 1);

-- Moderator: read, write, delete
INSERT INTO role_permissions (role_id, permission_id) VALUES
(4, 1), (4, 2), (4, 3);

-- Viewer: read only
INSERT INTO role_permissions (role_id, permission_id) VALUES
(5, 1);

-- ============================================================
-- INSERT USERS (Test Users)
-- ============================================================

-- Password: admin123 (hashed with Argon2id)
-- In PHP: password_hash('admin123', PASSWORD_ARGON2ID)
-- Hash: $2y$10$R9h7cIPz0gi.URNNV3kh2OPST9/PgBkqquzi.Ss7KIUgO2t0jKMUe

INSERT INTO users (user_id, email, username, password_hash, role_id, is_active, created_by, created_at) VALUES
(1, 'admin@example.com', 'admin', '$2y$10$R9h7cIPz0gi.URNNV3kh2OPST9/PgBkqquzi.Ss7KIUgO2t0jKMUe', 1, TRUE, NULL, NOW()),
(2, 'user@example.com', 'user1', '$2y$10$R9h7cIPz0gi.URNNV3kh2OPST9/PgBkqquzi.Ss7KIUgO2t0jKMUe', 2, TRUE, 1, NOW()),
(3, 'user2@example.com', 'user2', '$2y$10$R9h7cIPz0gi.URNNV3kh2OPST9/PgBkqquzi.Ss7KIUgO2t0jKMUe', 2, TRUE, 1, NOW()),
(4, 'guest@example.com', 'guest', '$2y$10$R9h7cIPz0gi.URNNV3kh2OPST9/PgBkqquzi.Ss7KIUgO2t0jKMUe', 3, TRUE, 1, NOW()),
(5, 'moderator@example.com', 'moderator', '$2y$10$R9h7cIPz0gi.URNNV3kh2OPST9/PgBkqquzi.Ss7KIUgO2t0jKMUe', 4, TRUE, 1, NOW());

-- ============================================================
-- INSERT AUDIT DATA (Sample)
-- ============================================================

INSERT INTO user_audit (user_id, action, after_state, changed_by, changed_at) VALUES
(1, 'INSERT', JSON_OBJECT('email', 'admin@example.com', 'role_id', 1), NULL, NOW()),
(2, 'INSERT', JSON_OBJECT('email', 'user@example.com', 'role_id', 2), 1, NOW()),
(3, 'INSERT', JSON_OBJECT('email', 'user2@example.com', 'role_id', 2), 1, NOW());

-- ============================================================
-- VERIFICATION QUERIES
-- ============================================================

-- Verify roles inserted
-- SELECT COUNT(*) as role_count FROM roles;
-- Expected: 5

-- Verify users inserted
-- SELECT COUNT(*) as user_count FROM users WHERE deleted_at IS NULL;
-- Expected: 5

-- Verify audit trail
-- SELECT COUNT(*) as audit_count FROM user_audit;
-- Expected: 3

-- Verify role permissions
-- SELECT r.role_name, GROUP_CONCAT(p.permission_name) as permissions
-- FROM roles r
-- LEFT JOIN role_permissions rp ON r.role_id = rp.role_id
-- LEFT JOIN permissions p ON rp.permission_id = p.permission_id
-- GROUP BY r.role_id, r.role_name;

