# Reference 08 — Real-World Examples by Industry

## Example 1: E-Commerce Platform (Music Streaming + Store)

**Entities:** User, Product, Order, OrderItem, Payment, Review, Wishlist

```sql
-- 3NF Normalized Schema

CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (role_id) REFERENCES roles(role_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id),
    INDEX idx_email (email),
    INDEX idx_username (username)
);

CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    artist_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artist_id) REFERENCES artists(artist_id),
    INDEX idx_artist_id (artist_id),
    CHECK (price > 0)
);

CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id),
    INDEX idx_user_id (user_id),
    INDEX idx_order_date (order_date),
    CHECK (status IN ('pending', 'paid', 'shipped', 'delivered', 'cancelled'))
);

CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (order_id, product_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    CHECK (quantity > 0)
);

CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100),
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    INDEX idx_transaction_id (transaction_id),
    CHECK (status IN ('pending', 'completed', 'failed', 'refunded'))
);

CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    UNIQUE KEY (product_id, user_id),
    CHECK (rating >= 1 AND rating <= 5)
);
```

---

## Example 2: Hospital Management System (HIPAA Compliant)

**Entities:** Patient, Doctor, Visit, Prescription, Diagnosis, Ward, Insurance

```sql
-- BCNF Normalized + HIPAA Encrypted

CREATE TABLE patients (
    patient_id INT PRIMARY KEY AUTO_INCREMENT,
    ssn_encrypted VARBINARY(255) NOT NULL UNIQUE,  -- ← encrypted AES-256
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    dob DATE NOT NULL,
    email_encrypted VARBINARY(255),  -- ← encrypted
    phone_encrypted VARBINARY(255),  -- ← encrypted
    address VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ssn_encrypted (ssn_encrypted)
);

CREATE TABLE doctors (
    doctor_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    license_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_specialty (specialty)
);

CREATE TABLE visits (
    visit_id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    visit_date DATETIME NOT NULL,
    visit_type VARCHAR(50) NOT NULL,  -- 'checkup', 'surgery', 'consultation'
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id),
    FOREIGN KEY (created_by) REFERENCES doctors(doctor_id),
    INDEX idx_patient_id (patient_id),
    INDEX idx_visit_date (visit_date),
    CHECK (visit_type IN ('checkup', 'surgery', 'consultation', 'emergency'))
);

CREATE TABLE diagnoses (
    diagnosis_id INT PRIMARY KEY AUTO_INCREMENT,
    icd_code VARCHAR(10) UNIQUE,  -- ICD-10 code
    name VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE visit_diagnoses (
    visit_id INT NOT NULL,
    diagnosis_id INT NOT NULL,
    PRIMARY KEY (visit_id, diagnosis_id),
    FOREIGN KEY (visit_id) REFERENCES visits(visit_id) ON DELETE CASCADE,
    FOREIGN KEY (diagnosis_id) REFERENCES diagnoses(diagnosis_id)
);

CREATE TABLE prescriptions (
    prescription_id INT PRIMARY KEY AUTO_INCREMENT,
    visit_id INT NOT NULL,
    medication_name VARCHAR(255) NOT NULL,
    dosage VARCHAR(100),
    frequency VARCHAR(100),
    duration_days INT,
    instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES visits(visit_id),
    INDEX idx_visit_id (visit_id)
);

-- ← HIPAA Compliance: Audit Trail
CREATE TABLE visit_audit (
    audit_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    visit_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,  -- 'INSERT', 'UPDATE', 'DELETE'
    changed_by INT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (visit_id) REFERENCES visits(visit_id),
    FOREIGN KEY (changed_by) REFERENCES doctors(doctor_id),
    INDEX (visit_id, changed_at)
);
```

---

## Example 3: CRM System

**Entities:** Contact, Account, Opportunity, Activity, Note, Pipeline

```sql
CREATE TABLE accounts (
    account_id INT PRIMARY KEY AUTO_INCREMENT,
    account_name VARCHAR(255) NOT NULL,
    industry VARCHAR(100),
    revenue_range VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id),
    INDEX idx_industry (industry)
);

CREATE TABLE contacts (
    contact_id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20),
    job_title VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    FOREIGN KEY (account_id) REFERENCES accounts(account_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    INDEX idx_account_id (account_id),
    INDEX idx_email (email)
);

CREATE TABLE opportunities (
    opportunity_id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT NOT NULL,
    opportunity_name VARCHAR(255) NOT NULL,
    pipeline_stage VARCHAR(50) NOT NULL,
    estimated_value DECIMAL(10,2),
    probability INT,  -- 0-100
    expected_close_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    FOREIGN KEY (account_id) REFERENCES accounts(account_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    INDEX idx_account_id (account_id),
    INDEX idx_pipeline_stage (pipeline_stage),
    CHECK (probability >= 0 AND probability <= 100)
);

CREATE TABLE activities (
    activity_id INT PRIMARY KEY AUTO_INCREMENT,
    contact_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,  -- 'call', 'email', 'meeting', 'task'
    description TEXT,
    activity_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    FOREIGN KEY (contact_id) REFERENCES contacts(contact_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    INDEX idx_contact_id (contact_id),
    INDEX idx_activity_date (activity_date),
    CHECK (activity_type IN ('call', 'email', 'meeting', 'task', 'note'))
);
```

---

## Normalization Comparison

### ❌ Denormalized (Bad for Updates)

```sql
CREATE TABLE sales_report (
    report_id INT,
    contact_id INT,
    contact_name VARCHAR(100),  -- ← denormalized, will be repeated
    contact_email VARCHAR(255),  -- ← repeated
    account_id INT,
    account_name VARCHAR(255),  -- ← repeated
    opportunity_id INT,
    opportunity_name VARCHAR(255),  -- ← repeated
    estimated_value DECIMAL(10,2),  -- ← repeated
    pipeline_stage VARCHAR(50)  -- ← repeated
);
-- Problem: If contact name changes, must update all rows
```

### ✅ 3NF Normalized (Good)

```sql
CREATE TABLE sales_report (
    report_id INT PRIMARY KEY,
    contact_id INT NOT NULL,
    opportunity_id INT NOT NULL,
    created_at TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contacts(contact_id),
    FOREIGN KEY (opportunity_id) REFERENCES opportunities(opportunity_id)
);

-- Name changes happen once:
UPDATE contacts SET first_name = 'John' WHERE contact_id = 1;
```

---

*Last Updated: 2026-06-11 | CoreMusic Examples*

