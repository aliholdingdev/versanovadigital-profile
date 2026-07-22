# Reference 09 — Anti-Patterns & Common Mistakes

## Anti-Pattern 1: Wide Tables (God Table)

❌ **BAD:**
```sql
CREATE TABLE users (
    user_id INT,
    email VARCHAR(255),
    phone VARCHAR(20),
    address VARCHAR(500),
    city VARCHAR(100),
    state VARCHAR(100),
    zip_code VARCHAR(10),
    country VARCHAR(100),
    billing_address VARCHAR(500),
    billing_city VARCHAR(100),
    ...
    -- 50+ columns in one table
);
```

**Problem:**
- Difficult to understand which columns relate
- Update anomalies (billing address in two places?)
- Slow queries (SELECT * loads unnecessary columns)
- Hard to version/evolve

✅ **GOOD:**
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE user_profiles (
    user_id INT PRIMARY KEY,
    phone VARCHAR(20),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE user_addresses (
    address_id INT PRIMARY KEY,
    user_id INT NOT NULL,
    address_type VARCHAR(50),  -- 'billing', 'shipping'
    street VARCHAR(500),
    city VARCHAR(100),
    state VARCHAR(100),
    zip_code VARCHAR(10),
    country VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
```

---

## Anti-Pattern 2: EAV (Entity-Attribute-Value)

❌ **BAD:**
```sql
CREATE TABLE products (
    product_id INT,
    attribute_name VARCHAR(50),
    attribute_value VARCHAR(255)
);

-- Data:
-- product_id=1, attribute='color', value='red'
-- product_id=1, attribute='size', value='M'
-- product_id=1, attribute='material', value='cotton'
```

**Problem:**
- Queries become complex (need multiple JOINs)
- Type checking impossible (color could be any string)
- Performance terrible (N rows per product)

✅ **GOOD:**
```sql
CREATE TABLE products (
    product_id INT PRIMARY KEY,
    name VARCHAR(255),
    color VARCHAR(50),
    size VARCHAR(20),
    material VARCHAR(100)
);
```

Or, if truly variable attributes:
```sql
CREATE TABLE products (
    product_id INT PRIMARY KEY,
    name VARCHAR(255),
    attributes JSON  -- {"color": "red", "size": "M"}
);
```

---

## Anti-Pattern 3: Missing Foreign Keys

❌ **BAD:**
```sql
CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    user_id INT  -- ← no FK constraint, could be invalid!
);

-- This is allowed, but wrong:
INSERT INTO orders (order_id, user_id) VALUES (1, 99999);  -- user_id 99999 doesn't exist!
```

**Problem:**
- Orphaned records (orders with non-existent users)
- Data integrity not enforced
- Deletion anomalies (delete user, orders remain)

✅ **GOOD:**
```sql
CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT
);

-- Now this fails:
INSERT INTO orders (order_id, user_id) VALUES (1, 99999);  -- ERROR!
```

---

## Anti-Pattern 4: Repeating Groups (1NF Violation)

❌ **BAD:**
```sql
CREATE TABLE products (
    product_id INT,
    name VARCHAR(100),
    tags TEXT  -- "electronics,gadgets,phones"
);

-- Query to find 'electronics':
SELECT * FROM products WHERE tags LIKE '%electronics%';  -- ← slow, fragile
```

**Problem:**
- Updates difficult (edit string parsing)
- Queries slow (LIKE instead of index)
- Can't enforce uniqueness (duplicate tags possible)

✅ **GOOD:**
```sql
CREATE TABLE products (
    product_id INT PRIMARY KEY,
    name VARCHAR(100)
);

CREATE TABLE product_tags (
    product_id INT NOT NULL,
    tag VARCHAR(50) NOT NULL,
    PRIMARY KEY (product_id, tag),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Query is simple & fast:
SELECT p.* FROM products p
INNER JOIN product_tags pt ON p.product_id = pt.product_id
WHERE pt.tag = 'electronics';
```

---

## Anti-Pattern 5: No Audit Trail

❌ **BAD:**
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    email VARCHAR(255),
    status VARCHAR(50)
);

-- If user status changes, we have no history of who changed it or when
UPDATE users SET status = 'suspended' WHERE user_id = 1;
-- Where's the audit trail?
```

**Problem:**
- No compliance (GDPR, HIPAA, PCI-DSS require audit)
- Can't debug data issues
- No accountability

✅ **GOOD:**
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    email VARCHAR(255),
    status VARCHAR(50),
    created_by INT,
    created_at TIMESTAMP,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id)
);

CREATE TABLE user_audit (
    audit_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(50),
    old_status VARCHAR(50),
    new_status VARCHAR(50),
    changed_by INT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (changed_by) REFERENCES users(user_id)
);
```

---

## Anti-Pattern 6: No Soft Delete

❌ **BAD:**
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    email VARCHAR(255)
);

-- Delete user (permanent, no recovery!)
DELETE FROM users WHERE user_id = 1;
```

**Problem:**
- Permanent deletion (recovery difficult)
- Cascading deletes (might delete unrelated data)
- Can't analyze why user left

✅ **GOOD:**
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    email VARCHAR(255),
    deleted_at TIMESTAMP NULL,
    deleted_by INT,
    FOREIGN KEY (deleted_by) REFERENCES users(user_id)
);

-- Soft delete (user still exists, just marked as deleted)
UPDATE users SET deleted_at = NOW(), deleted_by = 123 WHERE user_id = 1;

-- Queries automatically exclude deleted users:
SELECT * FROM users WHERE deleted_at IS NULL;
```

---

## Anti-Pattern 7: No Indexes

❌ **BAD:**
```sql
CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    user_id INT,  -- ← no index!
    created_at DATE  -- ← no index!
);

-- Queries scan entire table:
SELECT * FROM orders WHERE user_id = 123;  -- Full table scan!
SELECT * FROM orders WHERE created_at = '2026-06-11';  -- Full table scan!
```

**Problem:**
- O(n) complexity (slow for millions of rows)
- High CPU, high disk I/O
- Poor user experience

✅ **GOOD:**
```sql
CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    user_id INT,
    created_at DATE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Queries now use index (fast):
SELECT * FROM orders WHERE user_id = 123;  -- O(log n)
SELECT * FROM orders WHERE created_at = '2026-06-11';  -- O(log n)
```

---

## Anti-Pattern 8: Implicit Data Types

❌ **BAD:**
```sql
CREATE TABLE users (
    user_id INT,
    is_admin INT,  -- ← should be BOOLEAN!
    created_at VARCHAR(20)  -- ← should be TIMESTAMP!
);

-- Inconsistent data:
INSERT INTO users VALUES (1, 'yes', '2026-06-11');  -- ← accepted!
INSERT INTO users VALUES (2, 2, 'invalid date');  -- ← accepted!
```

**Problem:**
- No type validation (garbage data accepted)
- Queries difficult (must convert types)
- Bugs in application code

✅ **GOOD:**
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Type-safe:
INSERT INTO users VALUES (1, TRUE, NOW());  -- OK
INSERT INTO users VALUES (2, 'yes', '2026-06-11');  -- ERROR!
```

---

## Anti-Pattern 9: Missing Constraints

❌ **BAD:**
```sql
CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    amount DECIMAL(10,2),  -- ← no check, could be negative!
    status VARCHAR(50)  -- ← no check, could be any string!
);

-- Garbage data accepted:
INSERT INTO orders VALUES (1, -100, 'pending');  -- negative amount!
INSERT INTO orders VALUES (2, 50, 'invalid_status');  -- wrong status!
```

**Problem:**
- Invalid data in database
- Business logic must validate (duplicated effort)
- Hard to debug

✅ **GOOD:**
```sql
CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    amount DECIMAL(10,2),
    status VARCHAR(50),
    CHECK (amount > 0),
    CHECK (status IN ('pending', 'paid', 'shipped', 'cancelled'))
);

-- Invalid data rejected:
INSERT INTO orders VALUES (1, -100, 'pending');  -- ERROR!
INSERT INTO orders VALUES (2, 50, 'invalid_status');  -- ERROR!
```

---

## Anti-Pattern 10: Transitive Dependencies (3NF Violation)

❌ **BAD:**
```sql
CREATE TABLE students (
    student_id INT,
    city_id INT,
    city_name VARCHAR(100),  -- ← depends on city_id, not student_id!
    city_country VARCHAR(100)  -- ← also depends on city_id
);

-- Update anomaly: change city name, must update all 1000 students!
UPDATE students SET city_name = 'New York' WHERE city_id = 1;  -- 1000 rows updated!
```

**Problem:**
- Duplicate data (city name repeated per student)
- Update anomalies (change once, update 1000 rows)
- Potential for inconsistency

✅ **GOOD:**
```sql
CREATE TABLE students (
    student_id INT PRIMARY KEY,
    city_id INT NOT NULL,
    FOREIGN KEY (city_id) REFERENCES cities(city_id)
);

CREATE TABLE cities (
    city_id INT PRIMARY KEY,
    city_name VARCHAR(100),
    city_country VARCHAR(100)
);

-- Change city name once:
UPDATE cities SET city_name = 'New York' WHERE city_id = 1;  -- 1 row updated!
```

---

## Quick Check: Am I Following Best Practices?

```sql
☐ No wide tables (split into focused tables)
☐ No EAV pattern (use JSON if flexible)
☐ All FKs defined (every relationship constrained)
☐ No repeating groups (separate table for lists)
☐ Audit trail present (created_by, updated_at)
☐ Soft delete pattern (deleted_at column)
☐ Indexes on FKs and common queries
☐ Type safety (BOOLEAN not INT, TIMESTAMP not VARCHAR)
☐ Constraints enforced (CHECK, NOT NULL, UNIQUE)
☐ No transitive dependencies (3NF normalized)
```

---

*Last Updated: 2026-06-11 | CoreMusic Database Design*

