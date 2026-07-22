# Reference 02 — Normalization Rules (1NF → BCNF)

## Overview

Normalization is the process of organizing data to **eliminate redundancy** and **ensure consistency**.

**Key Principle:** A well-normalized database stores each piece of data exactly once.

---

## Normalization Forms (Progressive)

### First Normal Form (1NF)
**Definition:** Remove repeating groups. Each column contains atomic (indivisible) values.

**Violation Example:**
```sql
-- ❌ VIOLATES 1NF
CREATE TABLE products (
    product_id INT PRIMARY KEY,
    name VARCHAR(100),
    tags TEXT  -- "electronics,gadgets,phones" ← repeating group!
);
```

**Fix (1NF-Compliant):**
```sql
-- ✅ 1NF COMPLIANT
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
```

**Why:** Repeating groups make queries complex (`LIKE '%electronics%'`) and updates difficult (edit tag in string).

---

### Second Normal Form (2NF)
**Definition:** Be in 1NF, AND remove partial key dependencies.

**Concept:** If table has composite primary key (col1, col2), non-key columns must depend on **entire key**, not just part of it.

**Violation Example:**
```sql
-- ❌ VIOLATES 2NF
CREATE TABLE student_courses (
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    professor_name VARCHAR(100),  -- ← depends on course_id only, not (student, course)
    PRIMARY KEY (student_id, course_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);
```

**Problem:** Professor name repeated for every student in same course. Update anomaly: change professor, must update all 200 student rows.

**Fix (2NF-Compliant):**
```sql
-- ✅ 2NF COMPLIANT
CREATE TABLE student_courses (
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    PRIMARY KEY (student_id, course_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);

CREATE TABLE courses (
    course_id INT PRIMARY KEY,
    course_name VARCHAR(100),
    professor_name VARCHAR(100)
);
```

**Why:** Now professor lives in one place. Update is safe.

---

### Third Normal Form (3NF)
**Definition:** Be in 2NF, AND remove transitive dependencies.

**Concept:** Non-key column cannot depend on another non-key column.

**Violation Example:**
```sql
-- ❌ VIOLATES 3NF
CREATE TABLE students (
    student_id INT PRIMARY KEY,
    name VARCHAR(100),
    city_id INT,
    city_name VARCHAR(100),  -- ← depends on city_id, not student_id
    city_country VARCHAR(100) -- ← depends on city_id, not student_id
);
```

**Problem:** City name stored in student table. City changes, must update all student rows.

**Fix (3NF-Compliant):**
```sql
-- ✅ 3NF COMPLIANT
CREATE TABLE students (
    student_id INT PRIMARY KEY,
    name VARCHAR(100),
    city_id INT NOT NULL,
    FOREIGN KEY (city_id) REFERENCES cities(city_id)
);

CREATE TABLE cities (
    city_id INT PRIMARY KEY,
    city_name VARCHAR(100),
    city_country VARCHAR(100)
);
```

**Why:** City info stored once. Update is safe.

---

### Boyce-Codd Normal Form (BCNF)
**Definition:** Every determinant (column that determines other columns) must be a candidate key.

**Concept:** More strict than 3NF. Requires that EVERY dependency is on a full candidate key, not just primary key.

**Violation Example:**
```sql
-- ❌ VIOLATES BCNF
CREATE TABLE professor_courses (
    professor_id INT NOT NULL,
    course_id INT NOT NULL,
    time_slot VARCHAR(20),  -- ← time depends on professor? Violates BCNF if professor can't teach multiple courses
    PRIMARY KEY (professor_id, course_id),
    UNIQUE (course_id, time_slot)  -- ← course_id determines time_slot
);
```

**Problem:** Non-key column (time_slot) is determined by candidate key (course_id, time_slot), not primary key. Complex anomalies possible.

**Fix (BCNF-Compliant):**
```sql
-- ✅ BCNF COMPLIANT
CREATE TABLE professor_courses (
    professor_id INT NOT NULL,
    course_id INT NOT NULL,
    PRIMARY KEY (professor_id, course_id),
    FOREIGN KEY (professor_id) REFERENCES professors(professor_id),
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);

CREATE TABLE course_schedule (
    course_id INT NOT NULL,
    time_slot VARCHAR(20) NOT NULL,
    PRIMARY KEY (course_id, time_slot),
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);
```

**Why:** Now every dependency is on a candidate key. No anomalies possible.

---

## Decision Tree: Which Normal Form?

```
1NF?
├─ No repeating groups? YES → proceed
└─ Repeating groups (TEXT lists, JSON arrays)? NO → split to separate table

2NF?
├─ Single primary key? YES → proceed (can't violate 2NF)
├─ Composite key? Check: do all non-key columns depend on ENTIRE key?
│  ├─ Yes → proceed to 3NF
│  └─ No → move partial-dependent column to separate table with partial key

3NF?
├─ No transitive dependencies (non-key → non-key)?
│  ├─ Yes → proceed to BCNF
│  └─ No → move transitive column to separate table

BCNF?
├─ Every determinant is candidate key?
│  ├─ Yes → BCNF achieved, done
│  └─ No → rare, but may need to split table or refactor key
```

---

## Real-World Examples

### Example 1: E-Commerce Order Schema

**Scenario:** Product order system with customer, product, pricing.

**Unnormalized:**
```sql
CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    customer_name VARCHAR(100),
    customer_email VARCHAR(100),
    product_ids TEXT,  -- "P1,P2,P3" ← repeating group
    product_names TEXT,
    prices TEXT,  -- "99.99,49.99,19.99"
    total_price DECIMAL(10,2)  -- ← depends on prices above (transitive)
);
```

**Issues:**
- 1NF: repeating groups (product IDs as string)
- 2NF: N/A (no composite key)
- 3NF: total_price is transitive (depends on prices)

**3NF-Compliant:**
```sql
CREATE TABLE customers (
    customer_id INT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100)
);

CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_date DATETIME,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

CREATE TABLE order_items (
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT,
    unit_price DECIMAL(10,2),
    PRIMARY KEY (order_id, product_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE products (
    product_id INT PRIMARY KEY,
    name VARCHAR(100),
    price DECIMAL(10,2)
);
```

**Benefits:**
- Customer updated once (not per order)
- Product price changed once (not per order)
- Total calculated at query time (SUM(quantity * unit_price))

---

### Example 2: Hospital Patient Visit

**Scenario:** Patient visits doctor, gets diagnosis, gets prescription.

**Unnormalized:**
```sql
CREATE TABLE patient_visits (
    visit_id INT PRIMARY KEY,
    patient_id INT,
    patient_name VARCHAR(100),
    doctor_id INT,
    doctor_name VARCHAR(100),
    diagnosis_id INT,
    diagnosis_name VARCHAR(200),
    diagnosis_icd_code VARCHAR(20),
    prescription_ids TEXT,  -- "RX1,RX2,RX3"
    prescription_names TEXT,
    created_at DATETIME
);
```

**Issues:**
- 1NF: repeating groups (prescription IDs/names)
- 2NF: doctor_name depends on doctor_id (partial dependency)
- 3NF: diagnosis_name depends on diagnosis_id (transitive)

**BCNF-Compliant (with audit):**
```sql
CREATE TABLE patients (
    patient_id INT PRIMARY KEY,
    name VARCHAR(100),
    dob DATE,
    email VARCHAR(100)
);

CREATE TABLE doctors (
    doctor_id INT PRIMARY KEY,
    name VARCHAR(100),
    specialty VARCHAR(100)
);

CREATE TABLE visits (
    visit_id INT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    visit_date DATETIME,
    created_by INT,  -- audit: who created this record
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id)
);

CREATE TABLE diagnoses (
    diagnosis_id INT PRIMARY KEY,
    icd_code VARCHAR(20),
    name VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE visit_diagnoses (
    visit_id INT NOT NULL,
    diagnosis_id INT NOT NULL,
    PRIMARY KEY (visit_id, diagnosis_id),
    FOREIGN KEY (visit_id) REFERENCES visits(visit_id),
    FOREIGN KEY (diagnosis_id) REFERENCES diagnoses(diagnosis_id)
);

CREATE TABLE prescriptions (
    prescription_id INT PRIMARY KEY,
    visit_id INT NOT NULL,
    medication_name VARCHAR(100),
    dosage VARCHAR(50),
    duration_days INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES visits(visit_id)
);

-- Audit trail
CREATE TABLE visit_audit (
    audit_id INT PRIMARY KEY AUTO_INCREMENT,
    visit_id INT NOT NULL,
    action VARCHAR(50),  -- INSERT, UPDATE, DELETE
    changed_by INT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES visits(visit_id)
);
```

**Benefits:**
- Doctor name updated once
- Diagnosis name/code updated once
- Prescriptions stored separately (flexible, auditable)
- Complete audit trail of changes

---

## Denormalization Trade-Offs

**When to Denormalize (Performance > Consistency):**

✅ **Good candidates:**
- Reporting/Analytics (OLAP) ← denormalize for speed
- Heavy read, light write (>95% reads)
- Frequently aggregated values (total, count, average)
- Pre-calculated columns (e.g., order_item_count on order table)

❌ **Bad candidates:**
- Highly transactional (OLTP) ← keep normalized
- Frequent writes
- Complex consistency requirements (ACID-critical)

**Example: Denormalize for Analytics**

```sql
-- Normalized (transactional)
CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    customer_id INT
);

-- Denormalized copy (analytics)
CREATE TABLE orders_summary (
    order_id INT PRIMARY KEY,
    customer_id INT,
    customer_email VARCHAR(100),  -- ← denormalized (could cache)
    item_count INT,  -- ← denormalized (COUNT aggregation)
    total_price DECIMAL(10,2),  -- ← denormalized (SUM aggregation)
    INDEX (customer_id),
    INDEX (total_price)
);
```

**Maintenance:** Trigger-based update when orders change:
```sql
CREATE TRIGGER order_summary_update AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders_summary 
    SET item_count = item_count + 1
    WHERE order_id = NEW.order_id;
END;
```

---

## Normalization Audit Checklist

| Check | How to Verify | Fix If Violated |
|-------|---------------|-----------------|
| **1NF** | No TEXT/JSON columns with delimited lists | Split to separate table with FK |
| **2NF** | Composite keys: all non-keys depend on ENTIRE key | Move partial-dependent column out |
| **3NF** | Non-key columns: no dependencies between them | Move transitive column to separate table |
| **BCNF** | Every determinant is a candidate key | Refactor key or split table |
| **FK constraints** | All foreign keys defined | Add FK constraints |
| **Indexes** | FK columns, frequently queried columns indexed | Add indexes |
| **Audit trail** | created_by, created_at, updated_at, action columns | Add audit table |
| **Constraints** | PK, UNIQUE, CHECK, NOT NULL | Add constraints |

---

## Tools for Normalization Analysis

**Manual Process (CLI):**
1. Identify all columns per table
2. Identify all dependencies (col A determines col B?)
3. Identify partial dependencies (composite key only)
4. Identify transitive dependencies (non-key → non-key)
5. Check for repeating groups

**Automated (Skill Provides):**
- `normalize-checker.php` — Scans SQL, reports violations
- `security-audit.php` — Checks constraints, encryption, audit trails
- ER diagram generator — Visual dependency graph

---

## Next Steps

1. Analyze your schema against each form (1NF, 2NF, 3NF, BCNF)
2. Document violations with examples
3. Plan decomposition (split tables)
4. Test migration (backup first!)
5. Validate with `normalize-checker.php`

---

*Last Updated: 2026-06-11 | CoreMusic Database Architecture*

