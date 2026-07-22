# Reference 03 — Database Provider Dialects

## Provider Comparison Matrix

| Feature | MySQL | MSSQL | PostgreSQL | MongoDB |
|---------|-------|-------|-----------|---------|
| **SQL Support** | Standard | T-SQL | SQL + Extensions | No SQL |
| **Transactions** | InnoDB (ACID) | ACID | ACID | ACID (4.0+) |
| **Foreign Keys** | Yes | Yes | Yes | Document ref |
| **Indexes** | B-tree, Hash | B-tree, Columnstore | B-tree, Hash, GIN | Compound |
| **Row-Level Security (RLS)** | No | Yes | Yes (RLS) | No |
| **JSON Support** | JSON (5.7+) | JSON (2016+) | JSON, JSONB | Native |
| **Partitioning** | Range, Hash, List | Partition schemes | Declarative | Sharding |
| **Encryption at-rest** | Plugin (Community: no) | Transparent encryption | pgcrypto, pgss | Field-level |
| **Full-text search** | Yes | Yes | Yes | Text search |
| **Window functions** | 8.0+ | 2012+ | Yes | No |
| **Array type** | No | No | Yes | Native |
| **Default License** | GPL (free) | Commercial (or Community) | PostgreSQL (free) | SSPL (free) |

---

## MySQL Dialect

### Syntax Examples

**Create Table:**
```sql
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Insert with Auto-Increment:**
```sql
INSERT INTO users (email) VALUES ('user@example.com');
SELECT LAST_INSERT_ID();  -- Get inserted ID
```

**JSON Column:**
```sql
CREATE TABLE products (
    product_id INT PRIMARY KEY,
    attributes JSON,  -- {"color": "red", "size": "M"}
    KEY idx_attr (attributes->'$.color')
);

SELECT * FROM products WHERE JSON_EXTRACT(attributes, '$.color') = 'red';
```

**Partition by Range (5.7+):**
```sql
CREATE TABLE orders (
    order_id INT,
    order_date DATE,
    amount DECIMAL(10,2)
) PARTITION BY RANGE (YEAR(order_date)) (
    PARTITION p2020 VALUES LESS THAN (2021),
    PARTITION p2021 VALUES LESS THAN (2022),
    PARTITION p2022 VALUES LESS THAN (2023),
    PARTITION p_max VALUES LESS THAN MAXVALUE
);
```

### Strengths
- ✅ Easy to learn, widely deployed
- ✅ Auto-increment convenient
- ✅ Good default collation (utf8mb4_unicode_ci)
- ✅ InnoDB: ACID transactions, foreign keys

### Weaknesses
- ❌ Limited advanced features (no RLS, arrays, window functions <8.0)
- ❌ Community Edition has no encryption at-rest
- ❌ JSON support limited (slower than JSONB in PostgreSQL)

### CoreMusic Default
MySQL is CoreMusic default (used in music.coremusic.net, depends on this)

---

## MSSQL Dialect

### Syntax Examples

**Create Table:**
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY IDENTITY(1,1),
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE()
);

CREATE INDEX idx_email ON users(email);
```

**Identity (Auto-Increment):**
```sql
INSERT INTO users (email) VALUES ('user@example.com');
SELECT SCOPE_IDENTITY();  -- Get inserted ID
```

**JSON Column:**
```sql
CREATE TABLE products (
    product_id INT PRIMARY KEY,
    attributes NVARCHAR(MAX)  -- {"color": "red", "size": "M"}
);

SELECT * FROM products WHERE JSON_VALUE(attributes, '$.color') = 'red';
```

**Row-Level Security (RLS):**
```sql
CREATE SECURITY POLICY doctor_access_policy
ADD FILTER PREDICATE dbo.fn_doctorfilter(doctor_id)
ON dbo.visits;

-- Function limits visibility to doctor's own patients
CREATE FUNCTION fn_doctorfilter(@doctor_id INT) RETURNS TABLE
WITH SCHEMABINDING
AS RETURN
SELECT 1 WHERE @doctor_id = SESSION_CONTEXT(N'doctor_id');
```

**Columnstore Index (Analytics):**
```sql
CREATE NONCLUSTERED COLUMNSTORE INDEX idx_cs_orders
ON orders (order_date, amount, customer_id)
WHERE order_date >= '2022-01-01';
```

### Strengths
- ✅ Enterprise-grade RLS (excellent for multi-tenant)
- ✅ Columnstore indexes (10x faster analytics)
- ✅ Transparent encryption at-rest
- ✅ Strong RBAC (roles, permissions)

### Weaknesses
- ❌ Commercial license (expensive for small businesses)
- ❌ Windows-centric ecosystem
- ❌ Less flexible JSON support than PostgreSQL

---

## PostgreSQL Dialect

### Syntax Examples

**Create Table:**
```sql
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metadata JSONB  -- supports indexing
);

CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_metadata ON users USING GIN (metadata);
```

**Serial (Auto-Increment):**
```sql
INSERT INTO users (email) VALUES ('user@example.com');
SELECT lastval();  -- Get last sequence value
```

**JSONB Column (Better than JSON):**
```sql
CREATE TABLE products (
    product_id SERIAL PRIMARY KEY,
    attributes JSONB
);

-- Index nested JSON for fast queries
CREATE INDEX idx_attr_color ON products USING GIN (attributes);

SELECT * FROM products WHERE attributes->>'color' = 'red';
```

**Array Type:**
```sql
CREATE TABLE posts (
    post_id SERIAL PRIMARY KEY,
    title VARCHAR(255),
    tags TEXT[] ARRAY  -- ["tech", "news", "database"]
);

SELECT * FROM posts WHERE 'tech' = ANY(tags);
```

**Row-Level Security (RLS):**
```sql
ALTER TABLE visits ENABLE ROW LEVEL SECURITY;

CREATE POLICY doctor_visits_policy ON visits
FOR SELECT USING (doctor_id = current_user_id());

-- Combines with GRANT for fine-grained access
```

**Window Functions (Analytics):**
```sql
SELECT 
    doctor_id,
    visit_date,
    COUNT(*) OVER (PARTITION BY doctor_id ORDER BY visit_date) as running_visits
FROM visits;
```

**Partitioning by Range:**
```sql
CREATE TABLE visits (
    visit_id SERIAL,
    visit_date DATE,
    doctor_id INT,
    PRIMARY KEY (visit_id, visit_date)
) PARTITION BY RANGE (visit_date);

CREATE TABLE visits_2022 PARTITION OF visits
FOR VALUES FROM ('2022-01-01') TO ('2023-01-01');
```

### Strengths
- ✅ Most advanced SQL features (RLS, window functions, JSON, arrays)
- ✅ Free (PostgreSQL license)
- ✅ Excellent for healthcare/finance (complex domains)
- ✅ JSONB: fast JSON indexing + querying
- ✅ Strong full-text search (ts_vector, ts_query)

### Weaknesses
- ❌ More complex syntax (steeper learning curve)
- ❌ Smaller ecosystem than MySQL
- ❌ Less widely deployed in shared hosting

---

## MongoDB Dialect (NoSQL Document)

### Syntax Examples

**Create Collection & Insert:**
```javascript
db.users.insertOne({
    _id: ObjectId(),
    email: "user@example.com",
    created_at: new Date(),
    metadata: {
        phone: "555-1234",
        address: { city: "Seattle", zip: "98101" }
    }
});
```

**Query:**
```javascript
db.users.find({ email: "user@example.com" });
db.users.find({ "metadata.city": "Seattle" });
```

**Index:**
```javascript
db.users.createIndex({ email: 1 });
db.users.createIndex({ "metadata.city": 1 });
```

**Transaction (4.0+):**
```javascript
const session = db.getMongo().startSession();
session.startTransaction();
try {
    db.users.insertOne({ email: "user@example.com" }, { session });
    db.orders.insertOne({ user_id: user._id }, { session });
    session.commitTransaction();
} catch (e) {
    session.abortTransaction();
}
```

### Strengths
- ✅ Flexible schema (no predefined columns)
- ✅ Native nested objects (no join complexity)
- ✅ Good for semi-structured data (API responses, logs)
- ✅ Horizontal scaling (sharding) built-in

### Weaknesses
- ❌ No foreign keys (app-level responsibility)
- ❌ No transactions until 4.0 (even then, limited)
- ❌ Not ideal for relational data (CRM, ERP, healthcare)
- ❌ Data duplication (denormalization enforced)

---

## When to Choose Each

### Choose MySQL if:
- ✅ Traditional OLTP (orders, customer, products)
- ✅ Simple schema, good normalization sufficient
- ✅ Shared hosting (most providers support it)
- ✅ Budget-conscious (free, community-driven)
- ✅ Already using PHP/Laravel stack

### Choose MSSQL if:
- ✅ Enterprise Microsoft ecosystem (Windows Server, Office, Dynamics)
- ✅ RLS requirement (multi-tenant SaaS)
- ✅ Analytics workload (Columnstore indexes)
- ✅ Budget allows licensing
- ✅ Need transparent encryption at-rest

### Choose PostgreSQL if:
- ✅ Complex domains (healthcare, finance, GIS)
- ✅ Need advanced SQL (window functions, RLS, arrays)
- ✅ JSON/document hybrid workload
- ✅ Want best-of-breed free database
- ✅ Full-text search important

### Choose MongoDB if:
- ✅ Unstructured data (logs, events, API responses)
- ✅ Rapid iteration (schema changes frequent)
- ✅ Real-time analytics on nested data
- ✅ Horizontal scaling required (millions of documents)
- ❌ NOT for: relational integrity, strict transactions, complex joins

---

## Conversion Examples

### MySQL → PostgreSQL

```sql
-- MySQL
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PostgreSQL equivalent
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### MySQL → MSSQL

```sql
-- MySQL
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255)
);

-- MSSQL equivalent
CREATE TABLE users (
    user_id INT PRIMARY KEY IDENTITY(1,1),
    email VARCHAR(255)
);
```

### MySQL → MongoDB

```javascript
// Insert
db.users.insertOne({
    user_id: ObjectId(),
    email: "user@example.com"
});

// Query
db.users.findOne({ email: "user@example.com" });
```

---

## SQL Dialect Compatibility Issues

| Feature | MySQL | MSSQL | PostgreSQL | Note |
|---------|-------|-------|-----------|------|
| IDENTITY/AUTO_INCREMENT | INT AUTO_INCREMENT | IDENTITY(1,1) | SERIAL | Different syntax |
| String quote | Single ' or ` | Single ' | Single ' or double " | MySQL allows backtick |
| Date functions | DATE_ADD, DATE_SUB | DATEADD | date '+1 day' | Different functions |
| LIMIT/OFFSET | LIMIT 10 OFFSET 20 | TOP 10 OFFSET 20 | LIMIT 10 OFFSET 20 | Different syntax |
| String concat | CONCAT(a, b) | a + b | a \|\| b | Different operators |
| Boolean | TINYINT(1) | BIT | BOOLEAN | Different types |
| GUID | CHAR(36) | UNIQUEIDENTIFIER | UUID | Different types |

---

## Skill Workflow for Multi-Provider

When Skill generates schema:
1. **Detect provider** from question answer or project config
2. **Generate provider-specific SQL** using correct syntax
3. **Generate migration files** for target provider
4. **Validate dialect** (no MySQL syntax in PostgreSQL output)
5. **Include conversion notes** if migrating from existing provider

---

*Last Updated: 2026-06-11 | CoreMusic Database Architecture*

