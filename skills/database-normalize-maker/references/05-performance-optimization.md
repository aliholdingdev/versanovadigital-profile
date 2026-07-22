# Reference 05 — Performance Optimization (Indexing, Partitioning, Denormalization)

## Indexing Strategy

### Index Types by Provider

| Type | MySQL | PostgreSQL | MSSQL | Use Case |
|------|-------|-----------|-------|----------|
| **B-tree** | Default | Default | Default | General purpose, sorting |
| **Hash** | Yes | Yes | No | Exact match lookups |
| **GIN** | No | Yes | No | Array/JSON, full-text search |
| **GIST** | No | Yes | No | Range, geometric data |
| **Columnstore** | No | No | Yes | OLAP, analytics |
| **Covering** | All | Yes | Yes | All data in index (no table access) |

### Index Creation Best Practices

**Foreign Keys (Always):**
```sql
CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    user_id INT NOT NULL,  -- ← Always index FK
    product_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    INDEX idx_user_id (user_id),  -- ← Fast lookups
    INDEX idx_product_id (product_id)
);
```

**Frequently Queried Columns:**
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    email VARCHAR(255),  -- ← Often searched
    created_at TIMESTAMP,  -- ← Often filtered
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
);
```

**Compound Index (Multi-Column Queries):**
```sql
CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    user_id INT,
    created_at DATE,
    status VARCHAR(50),
    -- Compound index: user + date + status
    INDEX idx_user_date_status (user_id, created_at, status)
);

-- This index is used for:
SELECT * FROM orders WHERE user_id = 123 AND created_at = '2026-06-11';
SELECT * FROM orders WHERE user_id = 123 AND status = 'paid';

-- But NOT for:
SELECT * FROM orders WHERE created_at = '2026-06-11';  -- Doesn't start with user_id
```

**Covering Index (All Data in Index):**
```sql
CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(10,2),
    status VARCHAR(50)
);

-- Covering index: query doesn't access table, just index
CREATE INDEX idx_user_summary ON orders (user_id) 
INCLUDE (amount, status);  -- MSSQL syntax

-- This query never touches table (super fast):
SELECT user_id, amount, status FROM orders WHERE user_id = 123;
```

### Index Analysis

**Query Plan (MySQL):**
```sql
EXPLAIN SELECT * FROM orders WHERE user_id = 123 AND created_at > '2026-01-01';
```

Look for:
- ✅ `Using index` — Query satisfied by index alone
- ❌ `Using where` — Had to check table
- ❌ `Full table scan` — No index used (add one!)

---

## Partitioning Strategy (>100M rows)

### Range Partitioning (by Date)

**Use case:** Historical data (sales, logs, events)

```sql
CREATE TABLE orders (
    order_id INT,
    order_date DATE,
    amount DECIMAL(10,2),
    PRIMARY KEY (order_id, order_date)
) PARTITION BY RANGE (YEAR(order_date)) (
    PARTITION p2020 VALUES LESS THAN (2021),
    PARTITION p2021 VALUES LESS THAN (2022),
    PARTITION p2022 VALUES LESS THAN (2023),
    PARTITION p2023 VALUES LESS THAN (2024),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);

-- Benefits:
-- 1. Archiving: old partitions can be moved to cold storage
-- 2. Deletion: drop entire partition (fast)
-- 3. Query performance: WHERE order_date = '2021-06-01' only scans p2021 partition
```

### Hash Partitioning (by User ID)

**Use case:** Even distribution across servers (sharding prep)

```sql
CREATE TABLE user_sessions (
    user_id INT,
    session_id VARCHAR(36),
    login_time TIMESTAMP,
    PRIMARY KEY (user_id, session_id)
) PARTITION BY HASH (user_id) PARTITIONS 4;

-- Partitions: 0, 1, 2, 3 (user_id % 4)
-- Benefits:
-- 1. Even distribution across 4 partitions
-- 2. Horizontal scaling: add more partitions as users grow
```

### List Partitioning (by Country)

```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    email VARCHAR(255),
    country_code CHAR(2)
) PARTITION BY LIST (country_code) (
    PARTITION p_us VALUES IN ('US', 'CA', 'MX'),
    PARTITION p_eu VALUES IN ('DE', 'FR', 'UK', 'IT'),
    PARTITION p_asia VALUES IN ('CN', 'JP', 'IN'),
    PARTITION p_other VALUES IN (DEFAULT)
);
```

---

## Denormalization for Performance

### When to Denormalize

✅ **Good candidates:**
- Frequently aggregated values (SUM, COUNT, AVG)
- Heavy read workload (>95% reads)
- Reporting/Analytics tables
- Read-only snapshots

❌ **Bad candidates:**
- Transactional data (OLTP)
- Frequent writes
- Complex consistency requirements

### Pattern 1: Aggregate Column

**Normalized (slow for reporting):**
```sql
SELECT user_id, COUNT(*) as order_count
FROM orders
GROUP BY user_id;  -- Slow for millions of orders
```

**Denormalized (fast for reporting):**
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    email VARCHAR(255),
    order_count INT DEFAULT 0  -- ← denormalized
);

-- Trigger: update count on INSERT/DELETE
CREATE TRIGGER update_order_count AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    UPDATE users SET order_count = order_count + 1
    WHERE user_id = NEW.user_id;
END;
```

### Pattern 2: Materialized View (Snapshot)

**Use case:** Complex multi-table aggregation, queried frequently

```sql
-- Create separate table (not a view) for performance
CREATE TABLE user_order_summary (
    user_id INT PRIMARY KEY,
    total_orders INT,
    total_spent DECIMAL(10,2),
    avg_order_value DECIMAL(10,2),
    last_order_date DATE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_total_spent (total_spent)
);

-- Refresh periodically (e.g., daily)
-- INSERT INTO user_order_summary
-- SELECT user_id, COUNT(*), SUM(amount), AVG(amount), MAX(order_date)
-- FROM orders GROUP BY user_id;
```

### Pattern 3: Hybrid (Normalized + Denormalized)

**Use case:** High transaction rate + reporting needs

```sql
-- Normalized table (transactional)
CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2),
    created_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Denormalized summary (for reporting)
CREATE TABLE user_summary (
    user_id INT PRIMARY KEY,
    total_orders INT,
    total_spent DECIMAL(10,2),
    synced_at TIMESTAMP
);

-- Keep in sync with trigger or batch job
```

---

## Query Optimization Patterns

### N+1 Problem (Avoid!)

❌ **Slow (N+1 queries):**
```sql
-- Get user
SELECT * FROM users WHERE user_id = 123;

-- Then for each order (N separate queries!)
SELECT * FROM orders WHERE user_id = 123;
SELECT * FROM order_items WHERE order_id = 1;
SELECT * FROM order_items WHERE order_id = 2;
-- ... 100 more queries!
```

✅ **Fast (JOIN):**
```sql
SELECT u.*, o.*, oi.*
FROM users u
LEFT JOIN orders o ON u.user_id = o.user_id
LEFT JOIN order_items oi ON o.order_id = oi.order_id
WHERE u.user_id = 123;
```

### Covering Index (No Table Access)

```sql
-- Query only needs user_id, total_orders from summary
CREATE INDEX idx_user_summary ON users (user_id)
INCLUDE (total_orders, created_at);

-- This query never touches table:
SELECT user_id, total_orders FROM users WHERE user_id IN (1, 2, 3);
```

### Partial Index (Filter at Index)

```sql
-- Only index active users (smaller index)
CREATE INDEX idx_active_users ON users (email)
WHERE deleted_at IS NULL;

-- Faster than full index on users(email)
```

---

## Performance Audit Checklist

| Check | How to Verify | Fix If Missing |
|-------|---------------|----------------|
| **FK indexes** | `SHOW INDEXES FROM orders;` check user_id | `CREATE INDEX idx_user_id ON orders(user_id);` |
| **Query indexes** | `EXPLAIN SELECT ...` look for "Using index" | Add index on WHERE/JOIN columns |
| **Slow queries** | Check slow query log | Analyze with EXPLAIN, add indexes |
| **Covering indexes** | Queries return from index only | Add INCLUDE clause |
| **Partial indexes** | Indexes on filtered data | Add WHERE clause to index |
| **Aggregate columns** | Large table with COUNT/SUM queries | Denormalize counter column + trigger |
| **Partitioning** | Table >100M rows | Partition by date/range |

---

## Performance Monitoring

**MySQL:**
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;  -- Log queries >1 second

-- View slow queries
SELECT * FROM mysql.slow_log;
```

**PostgreSQL:**
```sql
-- Enable query statistics
CREATE EXTENSION pg_stat_statements;

-- View slowest queries
SELECT query, calls, mean_exec_time FROM pg_stat_statements
ORDER BY mean_exec_time DESC LIMIT 10;
```

---

*Last Updated: 2026-06-11 | CoreMusic Performance Architecture*

