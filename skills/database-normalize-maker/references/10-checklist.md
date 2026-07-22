# Reference 10 — Production-Grade Validation Checklist

## Pre-Generation Checklist

Before Skill generates SQL, verify:

```
☐ Requirements Gathered
  ☐ Project type identified (e-commerce, CRM, healthcare, etc.)
  ☐ Target scale understood (<1M, 1M-100M, >100M rows)
  ☐ Primary entities listed (minimum 5)
  ☐ Database provider selected (MySQL, PostgreSQL, MSSQL, MongoDB)
  ☐ Security level determined (public, PII, highly sensitive)
  ☐ Performance priority clear (OLTP, OLAP, real-time)
  ☐ Existing schema decision made (preserve, rewrite, hybrid)

☐ Research Complete
  ☐ Industry standards understood
  ☐ Vendor-specific features reviewed
  ☐ Security compliance checked (GDPR, HIPAA, PCI-DSS)
  ☐ Performance patterns researched
  
☐ Design Approved
  ☐ ER diagram reviewed & approved
  ☐ Normalization level understood (1NF, 2NF, 3NF, BCNF)
  ☐ Denormalization decisions justified
  ☐ Encryption strategy confirmed
  ☐ Audit trail requirements clear
```

---

## Post-Generation Validation

### Schema Structure Validation

```sql
☐ Primary Keys
  ☐ Every table has PK (user_id, product_id, etc.)
  ☐ PK is AUTO_INCREMENT (INT) or UUID
  ☐ PK name consistent (table_name_id pattern)

☐ Foreign Keys
  ☐ All relationships defined (FK constraints)
  ☐ FK column names match referenced table
  ☐ ON DELETE/UPDATE actions appropriate (RESTRICT, CASCADE, SET NULL)
  ☐ No orphaned records possible (FK enforced)

☐ Unique Constraints
  ☐ Email UNIQUE (no duplicate accounts)
  ☐ Username UNIQUE (if applicable)
  ☐ Business-critical columns UNIQUE
  ☐ Compound uniqueness handled (e.g., user + date)

☐ Check Constraints
  ☐ Status enums (only valid values)
  ☐ Numeric ranges (amount > 0, rating 1-5)
  ☐ Date sanity checks (end_date >= start_date)

☐ Not Null
  ☐ Required columns marked NOT NULL
  ☐ Optional columns allow NULL
  ☐ Defaults assigned where appropriate
```

### Normalization Validation

```sql
☐ First Normal Form (1NF)
  ☐ No repeating groups (no CSV in TEXT columns)
  ☐ No repeating tables (separate table for lists)
  ☐ All columns atomic (indivisible values)

☐ Second Normal Form (2NF)
  ☐ In 1NF ✓
  ☐ If composite key: all non-keys depend on ENTIRE key
  ☐ No partial key dependencies

☐ Third Normal Form (3NF)
  ☐ In 2NF ✓
  ☐ No transitive dependencies (non-key → non-key)
  ☐ No multi-step dependencies

☐ Boyce-Codd Normal Form (BCNF)
  ☐ In 3NF ✓
  ☐ Every determinant is candidate key
  ☐ No anomalies in multi-key scenarios
```

### Security Validation

```sql
☐ Encryption
  ☐ All PII columns identified
  ☐ Encryption marked (comment: encrypted_aes_256_gcm)
  ☐ Email: encrypted or hashed
  ☐ Phone: encrypted
  ☐ SSN: encrypted
  ☐ Health data: encrypted
  ☐ Financial data: encrypted
  ☐ Payment cards: tokenized (not stored in DB)

☐ Audit Trail
  ☐ created_by column present (FK to users/actors)
  ☐ created_at column present (TIMESTAMP DEFAULT CURRENT_TIMESTAMP)
  ☐ updated_by column present (FK)
  ☐ updated_at column present (TIMESTAMP ON UPDATE)
  ☐ deleted_by column present (for soft deletes)
  ☐ deleted_at column present (NULL = active, TIMESTAMP = deleted)
  ☐ audit_table present (for high-security tables)
  ☐ action column in audit_table (INSERT, UPDATE, DELETE)

☐ OWASP Top 10:2025
  ☐ No SQL injection possible (parameterized queries enforced at app level)
  ☐ Access control reflected in RBAC table
  ☐ Encryption strategy documented
  ☐ Input validation possible (constraints enforced)
  ☐ Cryptographic failures prevented (AES-256-GCM)
  ☐ No sensitive defaults (is_admin = FALSE, role = 'guest')
  ☐ Injection points minimized (FK constraints)
  ☐ Broken access control prevented (RLS if supported)
  ☐ Logging/monitoring ready (audit tables)
  ☐ Data integrity enforced (constraints, triggers)

☐ Compliance
  ☐ GDPR: consent_log table present
  ☐ GDPR: data retention period tracked
  ☐ GDPR: right to deletion possible (soft delete)
  ☐ HIPAA: encryption mandatory (✓)
  ☐ HIPAA: audit trail mandatory (✓)
  ☐ HIPAA: access control mandatory (✓)
  ☐ PCI-DSS: card data tokenized (not in DB)
  ☐ PCI-DSS: encryption mandatory (✓)
  ☐ PCI-DSS: audit trail mandatory (✓)
```

### Performance Validation

```sql
☐ Indexes
  ☐ PK index present (auto)
  ☐ FK columns indexed (user_id, product_id, etc.)
  ☐ WHERE clause columns indexed (status, created_at, etc.)
  ☐ JOIN columns indexed
  ☐ No missing indexes (query plan shows full table scan)
  ☐ No unnecessary indexes (overhead)

☐ Partitioning
  ☐ Table >100M rows: partitioning strategy defined
  ☐ Partition key: date range (for archiving)
  ☐ Partition size: balanced distribution

☐ Denormalization
  ☐ Aggregate columns justified (count, sum, avg)
  ☐ Trigger-based updates present (keep sync)
  ☐ Materialized views considered (for complex aggregations)
  ☐ No "premature denormalization" (normalize first, denormalize if needed)
```

### Consistency & Integrity

```sql
☐ Data Types
  ☐ BOOLEAN not INT
  ☐ TIMESTAMP not VARCHAR
  ☐ DECIMAL not FLOAT (for money)
  ☐ ENUM/VARCHAR for status (with CHECK)
  ☐ INT for IDs (not UUID unless necessary)

☐ Defaults
  ☐ Secure defaults (is_admin = FALSE, not TRUE)
  ☐ Timestamps auto-set (DEFAULT CURRENT_TIMESTAMP)
  ☐ Status defaults (DEFAULT 'pending', not NULL)
  ☐ Booleans default FALSE (safer than NULL)

☐ Naming Conventions
  ☐ Tables: plural (users, orders, products)
  ☐ Columns: snake_case (user_id, created_at)
  ☐ PK: table_id (user_id, product_id)
  ☐ FK: referencing_table_id (user_id, product_id)
  ☐ Boolean: is_* (is_admin, is_active)
  ☐ Timestamp: *_at (created_at, updated_at)
  ☐ Indexes: idx_* (idx_user_id, idx_status)
```

### Documentation

```sql
☐ Data Dictionary
  ☐ Every table documented
  ☐ Every column described (purpose, constraints, PII status)
  ☐ FK relationships explained
  ☐ Audit trail requirements noted
  ☐ Encryption status marked

☐ ER Diagram
  ☐ All tables shown
  ☐ All relationships shown (1:1, 1:N, N:M)
  ☐ Cardinality clear
  ☐ Key columns marked (PK, FK)

☐ Technical Report
  ☐ Design rationale (why this schema?)
  ☐ Normalization decisions (1NF-BCNF choices)
  ☐ Denormalization justification (if any)
  ☐ Performance strategy (indexes, partitions)
  ☐ Security strategy (encryption, audit, RLS)
  ☐ Migration plan (if existing schema)

☐ Validation Report
  ☐ BCNF compliance status
  ☐ OWASP compliance checklist
  ☐ Index analysis
  ☐ Performance audit results
  ☐ Security audit results
```

---

## CoreMusic Integration Checklist

```sql
☐ PHP Compatibility
  ☐ Schema compatible with PDO (no app-level raw SQL)
  ☐ All queries can be parameterized
  ☐ No stored procedures (if pure PHP preferred)
  ☐ Migrations in .ai/.sql/ folder
  ☐ Seed data in .ai/.sql/seed/

☐ Testing Ready
  ☐ Schema testable with PHPUnit
  ☐ Normalization checkable with normalize-checker.php
  ☐ Security auditable with security-audit.php
  ☐ ER diagram generated (schema-to-diagram.php)

☐ Wiki Updated
  ☐ .ai/log.md appended (new entry)
  ☐ .ai/decisions/ updated (new ADR if needed)
  ☐ .ai/.sql/ folder created & populated
  ☐ Technical report in markdown

☐ SOLID Compliance
  ☐ Single Responsibility: each table owns one concern
  ☐ Open/Closed: extensible without modifying existing
  ☐ Liskov Substitution: inheritance patterns valid
  ☐ Interface Segregation: no bloated join tables
  ☐ Dependency Inversion: FK constraints enforced
```

---

## Sign-Off Checklist (Final)

Before deploying schema:

```
☐ Schema structure: all sections validated ✓
☐ Normalization: BCNF verified ✓
☐ Security: OWASP + compliance verified ✓
☐ Performance: indexes + partitioning verified ✓
☐ CoreMusic: PHP + wiki integration verified ✓
☐ Documentation: ER diagram + data dictionary + report complete ✓
☐ Tests: unit tests + security audit scripts ready ✓
☐ Migration: up/down migrations ready ✓
☐ Seed data: sample data for testing ready ✓

APPROVED FOR PRODUCTION: _______________  DATE: ______________
```

---

*Last Updated: 2026-06-11 | CoreMusic Quality Assurance*

