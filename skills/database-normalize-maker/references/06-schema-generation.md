# Reference 06 — Schema Generation & Migration Strategy

## SQL Generation Process

### Detection Phase

Skill automatically detects target provider:

```
1. Read project .env → DB_CONNECTION=mysql|pgsql|sqlsrv
2. Scan config/database.php → driver = 'mysql'|'pgsql'|'sqlsrv'
3. Check composer.json → required php/mysql, pgsql, etc.
4. If ambiguous: ask user (Question 5)
```

### Template Selection

Based on provider, Skill selects:
- MySQL templates (AUTO_INCREMENT, CHARSET, ENGINE=InnoDB)
- PostgreSQL templates (SERIAL, EXTENSIONS, RLS)
- MSSQL templates (IDENTITY, NONCLUSTERED, Columnstore)
- MongoDB templates (Document schema, collections)

### Migration File Generation

```sql
-- 001_create_users_table.sql (up migration)
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 001_create_users_table_down.sql (down migration)
DROP TABLE users;
```

### Data Dictionary

```markdown
| Table | Column | Type | Nullable | Key | Description |
|-------|--------|------|----------|-----|-------------|
| users | user_id | INT | NO | PK | Unique user identifier |
| users | email | VARCHAR(255) | NO | UNIQUE | User email (unique) |
| users | created_at | TIMESTAMP | NO | | Record creation time |
```

### ER Diagram (Mermaid)

```
erDiagram
    USERS ||--o{ ORDERS : has
    ORDERS ||--|{ ORDER_ITEMS : contains
    PRODUCTS ||--o{ ORDER_ITEMS : "ordered in"
    
    USERS {
        int user_id PK
        string email UK
        datetime created_at
    }
    
    ORDERS {
        int order_id PK
        int user_id FK
        decimal amount
        string status
    }
```

---

## Migration Best Practices

### Zero-Downtime Migration (Backward Compatible)

**Phase 1: Add new column (safe)**
```sql
ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT FALSE;
```

**Phase 2: Migrate data (background job)**
```sql
UPDATE users SET email_verified = (email LIKE '%@%');  -- Example logic
```

**Phase 3: Update application (deploy)**
- App now uses email_verified column

**Phase 4: Remove old column (safe)**
```sql
ALTER TABLE users DROP COLUMN old_email_column;
```

### Up/Down Migrations

```sql
-- UP (forward)
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- DOWN (rollback)
DROP TABLE orders;
```

### Conditional Migrations (Safe)

```sql
-- Don't fail if already exists
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT
);

-- Check before drop
DROP TABLE IF EXISTS temp_users;
```

---

## Schema Versioning

### Version File

```
migrations/
├── 001_initial_schema.sql
├── 002_add_audit_columns.sql
├── 003_create_indexes.sql
├── 004_add_encryption.sql
└── schema_version.txt (contains "004")
```

### Version Control

```sql
CREATE TABLE schema_version (
    version_number INT PRIMARY KEY,
    description VARCHAR(255),
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hash VARCHAR(64)  -- SHA-256 of migration file (for validation)
);

INSERT INTO schema_version (version_number, description, hash)
VALUES (1, 'Initial schema', SHA2(FILE_READ('001_initial_schema.sql'), 256));
```

---

## Seed Data (Testing)

### Seed File Generation

```sql
-- 001_seed_users.sql
INSERT INTO users (user_id, email, created_at) VALUES
(1, 'admin@example.com', NOW()),
(2, 'user1@example.com', NOW()),
(3, 'user2@example.com', NOW());

-- 002_seed_products.sql
INSERT INTO products (product_id, name, price) VALUES
(1, 'Product A', 99.99),
(2, 'Product B', 49.99),
(3, 'Product C', 19.99);

-- 003_seed_orders.sql
INSERT INTO orders (order_id, user_id, created_at) VALUES
(1, 1, NOW()),
(2, 2, NOW()),
(3, 3, NOW());
```

### Seed Loading

**PHP (Laravel Seeder):**
```php
php artisan migrate:refresh --seed  // Run migrations + seed
```

**MySQL CLI:**
```bash
mysql -u user -p database < seed/001_seed_users.sql
mysql -u user -p database < seed/002_seed_products.sql
```

---

## Schema Backup Strategy

### Full Dump

```bash
# MySQL
mysqldump -u user -p database > backup.sql

# PostgreSQL
pg_dump -U user database > backup.sql

# MSSQL (PowerShell)
Backup-SqlDatabase -ServerInstance 'localhost\SQLEXPRESS' `
  -Database 'coremusic_db' `
  -BackupFile 'C:\backups\backup.bak'
```

### Incremental Backup

```sql
-- Log backup (MSSQL)
BACKUP LOG coremusic_db
TO DISK = 'C:\backups\coremusic_db.trn'
WITH INIT;
```

### Restore

```bash
# MySQL
mysql -u user -p database < backup.sql

# PostgreSQL
psql -U user -d database -f backup.sql
```

---

## Schema Change Validation

### Normalization Check Post-Migration

```sql
-- Verify 3NF compliance
SELECT TABLE_NAME, COUNT(*) as column_count
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'coremusic_db'
GROUP BY TABLE_NAME;

-- Check for missing indexes
SELECT TABLE_NAME, COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'coremusic_db'
AND COLUMN_NAME LIKE '%_id'
AND COLUMN_NAME NOT LIKE 'id'
AND TABLE_NAME NOT IN (
    SELECT TABLE_NAME FROM INFORMATION_SCHEMA.STATISTICS
);
```

### Constraint Validation

```sql
-- Verify all FKs exist
SELECT CONSTRAINT_NAME, TABLE_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE CONSTRAINT_TYPE = 'FOREIGN KEY'
AND REFERENCED_TABLE_NAME IS NULL;  -- Find broken FKs
```

---

*Last Updated: 2026-06-11 | CoreMusic Migration Architecture*

