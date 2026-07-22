# Reference 04 — Security Audit (OWASP + Encryption + Compliance)

## OWASP Top 10:2025 — Database Context

| # | Issue | Database Protection | Skill Validates |
|---|-------|--------------------|----|
| **A01** | Broken Access Control | Row-Level Security (RLS), roles, grant statements | ✅ FK constraints, RBAC columns |
| **A02** | Cryptographic Failures | Encryption at-rest (AES-256), TLS in-transit | ✅ PII column encryption |
| **A03** | Injection | Parameterized queries, no raw SQL in app | ✅ No SQL concatenation patterns |
| **A04** | Insecure Design | Security by default, fail-safe | ✅ NOT NULL constraints, defaults |
| **A05** | Security Misconfiguration | Secure DB config, restricted access | ✅ Audit tables, logging columns |
| **A06** | Vulnerable Components | Update DB version, check CVEs | ✅ Vendor-specific security features |
| **A07** | Authentication Failures | Password hashing, session management | ✅ Audit trail for login |
| **A08** | Data Integrity Failures | Transactions, constraints, audit trails | ✅ FK, PK, CHECK constraints |
| **A09** | Logging & Monitoring Failures | Audit tables, correlation IDs | ✅ created_by, action, changed_at |
| **A10** | SSRF | Trust boundaries, network segmentation | ✅ Comments on sensitive columns |

---

## Encryption Strategy

### Encryption at-Rest (Column-Level)

**Candidates for Encryption (PII):**
- Email addresses
- Phone numbers
- Social security numbers
- Credit card numbers (should use tokenization instead)
- Health information (HIPAA)
- Financial data (PCI-DSS)
- Addresses
- Government ID numbers

**Database-Level Encryption:**

**MySQL (Community):**
```sql
-- No built-in encryption. Use application-level (AES_ENCRYPT function)
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    email_encrypted VARBINARY(255),  -- Will store encrypted data
    email_key_version INT  -- For key rotation
);

-- PHP application handles encryption/decryption
// PHP: openssl_encrypt($email, 'AES-256-GCM', $key, 0, $iv, $tag);
```

**PostgreSQL (pgcrypto extension):**
```sql
-- Install pgcrypto extension
CREATE EXTENSION pgcrypto;

CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    email_encrypted BYTEA,
    created_at TIMESTAMP
);

-- Encrypt on INSERT
INSERT INTO users (email_encrypted) 
VALUES (pgp_sym_encrypt('user@example.com', 'master_password'));

-- Decrypt on SELECT
SELECT pgp_sym_decrypt(email_encrypted, 'master_password') FROM users;
```

**MSSQL (TDE - Transparent Data Encryption):**
```sql
-- Encrypt entire database (transparent, no app changes needed)
CREATE MASTER KEY ENCRYPTION BY PASSWORD = 'complex_password';

CREATE CERTIFICATE sql_cert WITH SUBJECT = 'TDE Certificate';

CREATE DATABASE ENCRYPTION KEY
WITH ALGORITHM = AES_256
ENCRYPTION BY SERVER CERTIFICATE sql_cert;

ALTER DATABASE coremusic_db
SET ENCRYPTION ON;
```

### Key Rotation Strategy

```sql
-- Track encryption key version
CREATE TABLE encryption_keys (
    key_version INT PRIMARY KEY,
    key_hash CHAR(64),  -- SHA-256 of key (for verification)
    created_at TIMESTAMP,
    retired_at TIMESTAMP NULL,
    algorithm VARCHAR(50)  -- AES-256-GCM, AES-128-CBC
);

INSERT INTO encryption_keys (key_version, key_hash, created_at, algorithm)
VALUES (1, SHA2('key_v1', 256), NOW(), 'AES-256-GCM');
```

**Rotation Process:**
1. Create new key, insert into encryption_keys
2. Re-encrypt existing data with new key (background job)
3. Mark old key as retired
4. Delete old key after retention period (e.g., 2 years)

---

## Audit Trail Implementation

### Standard Audit Columns (Every table that changes)

```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- ← audit
    created_by INT,  -- ← audit (user_id who created)
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  -- ← audit
    updated_by INT,  -- ← audit (user_id who last updated)
    deleted_at TIMESTAMP NULL,  -- ← soft delete
    deleted_by INT,  -- ← audit
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id),
    FOREIGN KEY (deleted_by) REFERENCES users(user_id)
);
```

### Immutable Audit Table

For high-security scenarios (healthcare, finance):

```sql
CREATE TABLE user_audit (
    audit_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(50),  -- INSERT, UPDATE, DELETE
    before_state JSON,  -- Old values
    after_state JSON,   -- New values
    changed_by INT NOT NULL,  -- Who made change
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),  -- IPv4 or IPv6
    user_agent TEXT,  -- Browser/app identifier
    correlation_id VARCHAR(36),  -- Trace request
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (changed_by) REFERENCES users(user_id),
    INDEX (user_id, changed_at),
    INDEX (changed_by, changed_at)
);

-- Trigger: Insert audit row on every change
CREATE TRIGGER user_insert_audit AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO user_audit (user_id, action, after_state, changed_by, changed_at)
    VALUES (NEW.user_id, 'INSERT', JSON_OBJECT('email', NEW.email), @current_user_id, NOW());
END;

CREATE TRIGGER user_update_audit AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF OLD.email != NEW.email THEN
        INSERT INTO user_audit (user_id, action, before_state, after_state, changed_by)
        VALUES (NEW.user_id, 'UPDATE', 
            JSON_OBJECT('email', OLD.email), 
            JSON_OBJECT('email', NEW.email), 
            @current_user_id);
    END IF;
END;
```

### Query Audit (Optional, for high-sensitivity)

```sql
CREATE TABLE query_log (
    log_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    query_type VARCHAR(10),  -- SELECT, INSERT, UPDATE, DELETE
    table_name VARCHAR(100),
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    execution_time_ms INT,
    rows_affected INT,
    correlation_id VARCHAR(36),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    INDEX (executed_at, user_id)
);

-- Slow query log (MySQL)
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;  -- Queries >2 seconds logged
```

---

## Constraint Strategy (Data Integrity)

### Primary Key (PK)

```sql
-- Every table must have PK
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    ...
);
```

### Foreign Key (FK)

```sql
-- Maintain referential integrity
CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE RESTRICT  -- Prevent orphans
        ON UPDATE CASCADE   -- Cascade updates
);
```

**Actions:**
- `RESTRICT` — Prevent delete if child rows exist (safest)
- `CASCADE` — Delete child rows when parent deleted (dangerous, use carefully)
- `SET NULL` — Set child FK to NULL when parent deleted (risky for NOT NULL columns)

### Unique Constraint

```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,  -- No duplicate emails
    phone VARCHAR(20) UNIQUE  -- Allow NULL, but only one per user
);
```

### Check Constraint

```sql
CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    amount DECIMAL(10,2),
    status VARCHAR(50),
    CHECK (amount > 0),  -- Positive amounts only
    CHECK (status IN ('pending', 'paid', 'cancelled'))
);
```

### Not Null Constraint

```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,  -- Email always required
    phone VARCHAR(20)  -- Phone optional
);
```

### Default Values

```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    status VARCHAR(20) DEFAULT 'active',  -- Default status
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_admin BOOLEAN DEFAULT FALSE  -- Default false
);
```

---

## Role-Based Access Control (RBAC)

### Column-Based RBAC

```sql
-- Separate table for user permissions
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    email VARCHAR(255),
    role_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

CREATE TABLE roles (
    role_id INT PRIMARY KEY,
    role_name VARCHAR(50),  -- 'admin', 'doctor', 'patient', 'viewer'
    permissions JSON  -- {"read": true, "write": false, "delete": false}
);

-- Query with RBAC check
SELECT u.* FROM users u
WHERE u.role_id = @current_role_id
AND u.role_id IN (1, 2);  -- Only allow specific roles
```

### Database-Level RBAC (PostgreSQL RLS)

```sql
-- Create roles
CREATE ROLE doctor_role;
CREATE ROLE patient_role;

-- Create policy
CREATE POLICY doctor_visits_policy ON visits
FOR SELECT USING (
    doctor_id = current_user_id()  -- Doctor only sees own visits
);

-- Enable RLS
ALTER TABLE visits ENABLE ROW LEVEL SECURITY;
GRANT SELECT ON visits TO doctor_role;
```

---

## Compliance Checklists

### GDPR (General Data Protection Regulation)

```sql
-- 1. Data identification
CREATE TABLE personal_data_inventory (
    table_name VARCHAR(100),
    column_name VARCHAR(100),
    is_pii BOOLEAN,
    data_category VARCHAR(50),  -- 'email', 'phone', 'health', 'biometric'
    retention_period INT  -- days
);

-- 2. Encryption
-- All PII encrypted with AES-256-GCM

-- 3. Right to deletion
-- Add soft-delete: deleted_at TIMESTAMP

-- 4. Right to access
-- Audit trail: user_audit table

-- 5. Consent logging
CREATE TABLE consent_log (
    user_id INT,
    consent_type VARCHAR(50),  -- 'marketing', 'profiling', 'analytics'
    consented BOOLEAN,
    consented_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
```

### HIPAA (Health Insurance Portability and Accountability Act)

```sql
-- 1. Encryption (required)
-- All PHI (Protected Health Information) encrypted

-- 2. Access controls
CREATE TABLE hipaa_access_log (
    log_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    patient_id INT,
    accessed_at TIMESTAMP,
    access_reason VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
);

-- 3. Audit controls
-- 2+ year audit retention

-- 4. Data integrity
-- Checksums on sensitive records
CREATE TABLE patient_audit (
    audit_id BIGINT PRIMARY KEY,
    patient_id INT,
    record_hash VARCHAR(64),  -- SHA-256 checksum
    verified_by INT,
    verified_at TIMESTAMP
);
```

### PCI-DSS (Payment Card Industry Data Security Standard)

```sql
-- 1. Never store full card numbers (use tokenization)
CREATE TABLE payment_tokens (
    token_id VARCHAR(50) PRIMARY KEY,
    card_last_four CHAR(4),  -- Only last 4 digits stored
    expiry_month INT,
    expiry_year INT,
    created_at TIMESTAMP
);

-- 2. Encryption
-- All card data encrypted with AES-256-GCM

-- 3. Audit
CREATE TABLE payment_transaction_audit (
    transaction_id BIGINT PRIMARY KEY,
    amount DECIMAL(10,2),
    token_id VARCHAR(50),
    status VARCHAR(50),
    created_at TIMESTAMP,
    FOREIGN KEY (token_id) REFERENCES payment_tokens(token_id)
);
```

---

## Security Audit Checklist

```sql
☐ Primary Key: Every table has PK?
☐ Foreign Keys: All relationships defined?
☐ Unique Constraints: No duplicates on critical columns?
☐ Check Constraints: Valid data values enforced?
☐ Not Null: Required columns marked NOT NULL?
☐ Defaults: Secure defaults (e.g., is_admin = false)?
☐ Encryption: PII columns encrypted?
☐ Audit Trail: created_by, updated_by, action logged?
☐ RBAC: Roles and permissions defined?
☐ Compliance: GDPR, HIPAA, PCI-DSS checks passed?
☐ RLS: Row-level security enabled (if provider supports)?
☐ Soft Delete: deleted_at column present?
☐ Indexes: Performance columns indexed?
```

---

## Skill Validation

When Skill validates security:
1. ✅ Check for PII columns (email, phone, SSN, health data)
2. ✅ Verify encryption marked (encrypted_aes_256_gcm comment)
3. ✅ Validate audit columns present (created_by, updated_at)
4. ✅ Confirm constraints (PK, FK, UNIQUE, CHECK, NOT NULL)
5. ✅ Check for soft-delete pattern (deleted_at)
6. ✅ Verify RLS enabled (PostgreSQL/MSSQL)
7. ✅ Scan for hardcoded secrets (fail immediately)
8. ✅ Report OWASP Top 10 compliance status

---

*Last Updated: 2026-06-11 | CoreMusic Security Architecture*

