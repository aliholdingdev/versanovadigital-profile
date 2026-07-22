# Reference 01 — Requirements Gathering Protocol

## 8-Question Framework

This protocol ensures comprehensive understanding of project needs before ANY design work.

---

## Question 1: Project Type

**Question:** What type of project is this?

**Options:**
- (A) **E-Commerce** — Product catalog, shopping cart, orders, payments, inventory
- (B) **CRM** — Contacts, accounts, opportunities, activities, pipelines
- (C) **ERP** — Purchase orders, invoices, inventory, accounting, manufacturing
- (D) **Social Media** — Users, posts, comments, likes, followers, messaging
- (E) **Healthcare/Hospital** — Patients, doctors, visits, prescriptions, medical records
- (F) **HRM/Payroll** — Employees, departments, salaries, attendance, benefits
- (G) **CMS** — Articles, pages, categories, comments, media, users
- (H) **Other** — Describe (financial, logistics, education, etc.)

**Why This Matters:**
- Each domain has standard entities & relationships
- Security requirements vary (HIPAA for healthcare, PCI-DSS for e-commerce)
- Normalization strategies differ (e-commerce denormalizes for performance, healthcare emphasizes audit trails)

**Follow-up:** If "Other", skill will research similar industry standards.

---

## Question 2: Target Audience & Scale

**Question:** Who will use this system?

**Options:**
- (A) **Enterprise** — 1000+ concurrent users, 24/7 uptime, multi-tenant, global scale
- (B) **SME (Small-Medium)** — 100-1000 users, standard business hours, single tenant
- (C) **Startup** — <100 users, MVP phase, rapid iteration
- (D) **Consumer/Public** — Unknown user base, anonymous access, viral potential

**Why This Matters:**
- Enterprise: needs replication, partitioning, RLS, advanced security
- SME: standard OLTP, basic backups
- Startup: quick iteration, horizontal scalability
- Consumer: high concurrency, caching strategy

**Follow-up (Part 2):**
**Expected Scale?**
- (A) <1M rows total (small project)
- (B) 1M-100M rows (medium, may need indexing strategy)
- (C) >100M rows (requires partitioning, archiving)
- (D) Real-time streaming (event-sourcing, time-series database)
- (E) Analytics warehouse (OLAP, columnar indexes)

---

## Question 3: Primary Entities

**Question:** What are the main data objects in this system?

**Format:** List 5-10 primary entities

**Examples:**

**E-Commerce:**
```
- User (customer profile)
- Product (catalog item)
- Order (purchase transaction)
- OrderItem (line items)
- Payment (payment method)
- Inventory (stock levels)
- Review (customer feedback)
```

**CRM:**
```
- Contact (person)
- Account (company)
- Opportunity (sales deal)
- Activity (task, call, email)
- Pipeline (sales stage)
- Note (internal comment)
```

**Hospital:**
```
- Patient (medical record)
- Doctor (physician)
- Visit (appointment/admission)
- Prescription (medication order)
- Diagnosis (condition)
- Ward (physical location)
```

**Why This Matters:**
- Guides ER model design
- Identifies missing entities early
- Reveals relationships (1:1, 1:N, N:M)
- Shows audit requirements (Patient visits must be immutable)

**Follow-up:** Skill will ask about relationships between entities.

---

## Question 4: Expected Scale & Growth

**Question:** How much data will this system handle?

**Part A: Rows per primary entity**
```
User table:             1K / 10K / 100K / 1M / 10M+ rows?
Order table:            100 / 1K / 10K / 100K / 1M+ rows?
{each main entity}
```

**Part B: Growth rate**
```
New rows per month:     10 / 100 / 1K / 10K / 100K+
Retention period:       3 months / 1 year / 5 years / forever
Archive strategy?       None / manual / automated
```

**Part C: Concurrency**
```
Typical concurrent users:       10 / 100 / 1K / 10K+
Peak concurrent connections:    50 / 500 / 5K+
Transactions per second:        1 / 10 / 100 / 1K+
```

**Why This Matters:**
- <1M rows: simple indexes sufficient
- 1M-100M rows: need covering indexes, query optimization
- >100M rows: need partitioning, denormalization, archiving
- High concurrency: need transaction isolation strategy, connection pooling
- Real-time: need event sourcing or time-series strategy

---

## Question 5: Database Provider

**Question:** Which database system will you use?

**Options:**
- (A) **MySQL** — Default, widely deployed, good for OLTP
- (B) **MSSQL** — Enterprise Windows, strong security, columnstore
- (C) **PostgreSQL** — Advanced features (JSON, arrays, RLS, full-text search)
- (D) **MongoDB** — Document-oriented, flexible schema (for semi-structured data)
- (E) **Auto-detect** — Skill will scan project for hints (PDO config, Eloquent models, etc.)

**Why This Matters:**
- Each provider has different SQL syntax, performance characteristics, feature set
- MySQL: easy to learn, limited advanced features
- MSSQL: strong RBAC, RLS, performance, but Windows-centric
- PostgreSQL: most advanced (RLS, JSON, arrays, Window functions)
- MongoDB: good for unstructured data, poor for complex transactions

**Follow-up:**
If Auto-detect selected, skill will scan:
- `config/database.php` or `.env` for DB_CONNECTION
- `composer.json` for `php/mysql`, `freetds`, `mongodb/mongodb`
- Migration files (Eloquent, Laravel-style naming)

---

## Question 6: Security & Compliance

**Question:** What is your security level?

**Part A: Data sensitivity**
- (A) **Public data only** — No encryption needed, basic access control
- (B) **Internal data** — Employee records, customer names, not highly sensitive
- (C) **PII involved** — Personal identifiable info (SSN, phone, address), email
- (D) **Highly sensitive** — Healthcare (HIPAA), Finance (PCI-DSS), Government

**Part B: Compliance requirements**
- GDPR? (EU personal data)
- HIPAA? (US healthcare)
- PCI-DSS? (Payment cards)
- SOC 2? (Audit trails)
- Industry-specific? (Finance, government)

**Part C: Encryption**
- No encryption (skip)
- Application-level (AES-256-GCM in PHP)
- Database-level (Transparent Data Encryption)
- Column-level (encrypt sensitive columns)
- At-rest AND in-transit

**Part D: Audit logging**
- No audit needed
- Basic audit (who, what, when)
- Complete audit (who, what, when, why, from where)
- Immutable audit trail (append-only, tamper-proof)

**Why This Matters:**
- Public data: simple schema, minimal security columns
- PII: mandatory encryption, audit trails, GDPR compliance
- Healthcare: HIPAA requires encryption, audit, patient consent logs
- Finance: PCI-DSS requires encryption, tokenization, audit trail
- Skill will auto-generate audit columns, encryption strategy

---

## Question 7: Performance Priority

**Question:** What's your primary performance goal?

**Part A: Access pattern**
- (A) **OLTP** — Transactional (online processing), many small reads/writes, Acid transactions
- (B) **OLAP** — Analytical (data warehouse), large batch reads, complex joins
- (C) **Mixed** — Some transactional, some reporting
- (D) **Real-time** — Streaming data, sub-second latency requirements

**Part B: Read vs Write**
- Heavy read (95% read, 5% write) → denormalize for reads
- Balanced (50/50) → normalize for consistency
- Heavy write (95% write, 5% read) → normalize to avoid contention

**Part C: Query patterns**
- Mostly simple (single table, indexed lookups)
- Mostly complex (multi-table joins, aggregations)
- Mixed

**Why This Matters:**
- OLTP + heavily normalized = transactions safe, reads slower
- OLAP + denormalized = reads fast, maintenance slower
- Real-time + heavy write = need event sourcing, time-series tricks
- Skill adjusts denormalization strategy based on this

**Follow-up:** Skill will ask about specific slow queries, reporting needs.

---

## Question 8: Existing Schema Handling

**Question:** Does a schema already exist?

**Part A: Existing schema presence**
- (A) **No schema** — Green-field project, design from scratch
- (B) **Schema exists** — Preserve & audit (analyze, improve, suggest changes)
- (C) **Partial schema** — Some tables exist, others need design
- (D) **Legacy system** — Old schema, need modernization

**Part B: If schema exists, what to do?**
- (A) **Preserve & Audit** — Keep existing, analyze for issues, suggest improvements
- (B) **Rewrite** — Redesign completely (data migration needed)
- (C) **Hybrid** — Keep some tables, redesign others (careful phasing)
- (D) **User decides per-table** — Some preserved, some rewritten

**Part C: Data migration**
- No data exists (new tables)
- Existing data (need backup, migration script, validation)
- Zero-downtime required (blue-green strategy)

**Why This Matters:**
- Preserve & audit: safest (no data loss), but constraints from old design
- Rewrite: cleanest, but risky (data migration complexity)
- Hybrid: practical (migrate table-by-table)
- Skill will generate appropriate migration strategies

**Follow-up:** If existing schema, skill will:
1. Load & parse SQL
2. Analyze normalization level
3. Identify security gaps
4. Propose improvements
5. Generate migration plan

---

## Summary Decision Tree

```
Q1: Type → Determines standard entities, security baseline
Q2: Scale → Determines partitioning, indexing, archiving strategy
Q3: Entities → Determines ER model, relationships
Q4: Data size → Determines performance strategy (index vs denormalize)
Q5: Provider → Determines SQL dialect, feature set availability
Q6: Security → Determines encryption, audit, compliance columns
Q7: Performance → Determines normalization level (3NF vs denormalized)
Q8: Existing → Determines preservation vs migration strategy
```

---

## Example Answers

### E-Commerce Startup

```
Q1: A (E-Commerce)
Q2: B (SME, 100-1000 users), B (1M-100M rows)
Q3: Product, Order, Customer, Cart, Review, Payment
Q4: 100K users, 1M orders/month, 500 concurrent
Q5: A (MySQL)
Q6: B (PII involved, customer emails/addresses), PCI-DSS (payment card data)
    Encryption: Column-level (card data), Basic audit
Q7: A (OLTP), Balanced, Simple queries
Q8: A (No existing schema)
```

**Implication:** Design normalized (3NF) for transaction safety, add indexes on user_id/order_id/product_id, encrypt payment data, add audit table for compliance.

---

### Hospital Healthcare System

```
Q1: E (Healthcare)
Q2: A (Enterprise, 1000+), C (>100M rows)
Q3: Patient, Doctor, Visit, Prescription, Diagnosis, Ward, Insurance
Q4: 100K patients, 1M visits/year, 10K concurrent
Q5: C (PostgreSQL, RLS support)
Q6: D (Highly sensitive), HIPAA compliance, At-rest encryption, Complete audit
Q7: A (OLTP), Balanced, Complex joins (patient history)
Q8: B (Preserve existing Patient table, redesign Visit/Prescription)
```

**Implication:** Design BCNF-normalized for consistency, add RLS for doctor access control, encrypt all PII, audit every visit (immutable), add patient consent log, keep Patient table but refactor visits, create migration plan.

---

## Next Steps After Questions

1. Skill runs **deep-research** (industry standards for your domain)
2. Skill creates **ER diagram** (visual representation)
3. Skill shows **normalization analysis** (1NF-BCNF for each table)
4. User **approves design** (or iterates)
5. Skill **generates SQL** (provider-specific)
6. Skill **validates** (BCNF, OWASP, perf audit)

---

*Last Updated: 2026-06-11 | CoreMusic Database Architect Skill*

