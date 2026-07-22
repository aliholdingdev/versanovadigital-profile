---
name: database-normalize-maker
type: database-architect
category: schema-design, normalization, migration, security
version: 1.0
author: CoreMusic Skill Factory
status: production
requires: [brainstorming, deep-research, superpowers-web-searcher]

metadata:
  domains: [php, backend, database, security, architecture]
  languages: [sql, php]
  providers: [mysql, mssql, postgresql, mongodb, auto-detect]
  complexity: high
  coremusic_integration: true
  owasp_compliance: true
  solid_principles: true

triggers:
  - schema
  - sql
  - normalize
  - normalizasyon
  - veritabanı
  - veri tabanı
  - veritabanı oluştur
  - veri tabanı kur
  - tablo oluştur
  - database oluştur
  - db oluştur
  - normalize et
  - db normalize
  - tablo normalize
  - denormalize
  - veri modeli
  - er diagram
  - migration
  - migration yaz
  - veri akışı
  - veri bütünlüğü
  - indeks
  - constraint
  - foreign key
  - primary key
  - bcnf
  - 3nf
  - 2nf
  - 1nf
  - seed veri
  - backup
  - replikasyon
  - encryption
  - audit
  - schema audit
  - database design
  - veri tabanı tasarım

description: |
  🏗️ **database-normalize-maker** — Enterprise Database Lifecycle Management
  
  Multi-domain database architecture skill covering complete lifecycle: schema design,
  normalization (1NF/2NF/3NF/BCNF), migration management, security hardening,
  performance optimization, and technical documentation.
  
  **Scope:**
  - Project analysis (scan existing schemas, migrations, ORM models)
  - Requirements gathering (8-question protocol, industry analysis)
  - Research (deep-research on DB best practices, vendor specifics)
  - Design (ER modeling, normalization strategy, constraint rules)
  - Generation (provider-specific SQL, migrations, seed data, diagrams)
  - Validation (normalization audit, security audit, performance review)
  
  **Providers:** MySQL, MSSQL, PostgreSQL, MongoDB, auto-detect
  
  **Outputs:** SQL scripts, migrations, ER diagrams, data dictionary,
  technical reports, security audits, PHP validation scripts
  
  **Integration:** Works with brainstorming, deep-research, web-searcher,
  CoreMusic .ai/ vault, prompt-maker for requirement structuring
---

# database-normalize-maker Skill

## 🎯 Core Purpose

This skill transforms **raw project requirements into production-grade database architectures**.

It is NOT a simple SQL generator. It is a **Database Architect Assistant** that:

1. **Analyzes** existing database structures (or absence thereof)
2. **Questions** your requirements deeply (8-step protocol)
3. **Researches** industry standards & vendor best practices
4. **Designs** normalized, secure, performant schemas
5. **Generates** provider-specific SQL (MySQL, MSSQL, PostgreSQL, MongoDB)
6. **Validates** against BCNF, OWASP, CoreMusic rules
7. **Documents** ER diagrams, data dictionaries, migration strategies

---

## 📊 Workflow — 6 Phases

### **Phase 1: Project Analysis (Silent)**
Skill automatically:
- Scans `.ai/.sql/` directory for existing schemas
- Reads `.ai/brain.md` for past DB decisions
- Checks project root for migrations, ORM models, entity classes
- Reads `.ai/decisions/` for normalization/security ADRs
- Detects database provider hints (PDO config, Eloquent models, etc.)

**Output:** Context summary, existing asset inventory

---

### **Phase 2: Requirement Gathering (Interactive)**

User must answer **8 questions:**

```
1. Project Type?
   a) E-commerce  b) CRM  c) ERP  d) Social Media  e) Hospital/Healthcare
   f) HRM/Payroll g) CMS  h) Other → Describe

2. Target Audience?
   a) Enterprise (1000+ users)  b) SME (100-1000)  c) Startup (<100)
   d) Consumer/Public

3. Primary Entities?
   (List main data objects: user, product, order, patient, content, etc.)

4. Expected Scale?
   a) <1M rows total  b) 1M-100M rows  c) >100M rows
   d) Real-time streaming  e) Analytics warehouse

5. Database Provider?
   a) MySQL  b) MSSQL  c) PostgreSQL  d) MongoDB  e) Auto-detect from project

6. Security Level?
   a) Public data only  b) PII involved  c) Highly sensitive (healthcare, finance)
   Encryption needed? Audit logging? RLS?

7. Performance Priority?
   a) OLTP (transactional)  b) OLAP (analytical)  c) Mixed
   d) Real-time requirements?

8. Existing Schema?
   a) Preserve & audit  b) Rewrite from scratch  c) Hybrid (preserve some, redesign other)
```

**Output:** Structured requirements object

---

### **Phase 3: Research (Automatic)**

Skill triggers **deep-research**:
- Industry standard schemas (e.g., "e-commerce database normalization best practices 2026")
- Vendor-specific optimizations (MySQL partitioning, MSSQL columnstore, PostgreSQL JSON)
- OWASP guidelines (encryption, audit trails, RLS)
- NIST data protection standards
- Performance patterns (indexing, denormalization trade-offs)

**Output:** Research findings, cited sources, best practices summary

---

### **Phase 4: Design (Interactive)**

Skill creates:
- **Entity-Relationship Model** (ASCII ER diagram)
- **Normalization Assessment** (1NF → BCNF analysis per table)
- **Constraint Strategy** (PK, FK, UNIQUE, CHECK, DEFAULT)
- **Index Strategy** (covering, partial, compound indexes)
- **Denormalization Decisions** (justified trade-offs for performance)
- **Security Architecture** (encryption columns, audit tables, RLS)

User reviews & approves design before generation.

**Output:** Finalized design document, ER diagram, constraint matrix

---

### **Phase 5: Generation (Automatic)**

Skill generates:
- **schema.sql** — Provider-specific SQL (MySQL, MSSQL, PostgreSQL dialect)
- **migrations/** — Up/down migration files
- **seed.sql** — Sample data for testing
- **data-dictionary.md** — Table/column descriptions
- **er-diagram.md** — Mermaid ER visualization
- **technical-report.md** — Architecture rationale, trade-offs, future scalability

**Output Location:** Default `.ai/.sql/` or user-specified path

---

### **Phase 6: Validation (Automatic)**

Skill validates:
- **Normalization Audit** — Each table checked against 1NF, 2NF, 3NF, BCNF
- **Security Audit** — OWASP compliance, encryption coverage, audit trail presence
- **Performance Audit** — Index coverage, query patterns, potential N+1 issues
- **PHP Compatibility** — `declare(strict_types=1)`, PDO readiness, no raw SQL
- **CoreMusic Rules** — SOLID principles, Layered Architecture, Hexagonal patterns

Generates **validation report** with pass/fail for each criterion.

---

## 📁 Output Structure

```
.ai/.sql/
├── {project-name}-schema.sql          ← Main schema
├── {project-name}-migrations/
│   ├── 001_create_tables.sql
│   ├── 002_add_indexes.sql
│   └── 003_add_audit_tables.sql
├── {project-name}-seed.sql            ← Test data
├── {project-name}-data-dictionary.md  ← Column descriptions
├── {project-name}-er-diagram.md       ← Mermaid ER visualization
├── {project-name}-technical-report.md ← Design rationale
└── {project-name}-validation-report.md ← Security + perf audit
```

---

## 🔐 CoreMusic Integration

### Mandatory Rules (Auto-Injected)

**PHP Layer:**
- `declare(strict_types=1)` required in PHP validation scripts
- PDO + prepared statements mandatory (no raw SQL)
- Constructor Injection pattern
- Repository/Service layer separation
- Structured logging with correlation IDs

**Security (OWASP Top 10:2025):**
- Parameterized queries (no SQL injection)
- Encryption at-rest (AES-256-GCM for PII columns)
- Audit trails (who, what, when, why)
- Role-Based Access Control (RBAC)
- Row-Level Security (RLS) where supported

**Architecture (SOLID + DDD):**
- Single Responsibility: each table owns one concern
- Open/Closed: extensible without modifying existing tables
- Liskov Substitution: inheritance/hierarchy consistency
- Interface Segregation: no bloated join tables
- Dependency Inversion: domain model → infra, not reverse

**Performance:**
- Explicit indexes on FK, frequently queried columns
- Covering indexes for read-heavy queries
- Partition strategy for >100M row tables
- Archive/purge strategy for audit tables
- Denormalization justified & documented

---

## 🧪 Validation Scripts (PHP)

### `normalize-checker.php`
```bash
php scripts/normalize-checker.php <schema.sql>
```
Analyzes schema against 1NF/2NF/3NF/BCNF:
- Detects functional dependencies
- Identifies transitive dependencies (2NF violations)
- Flags partial key dependencies (2NF violations)
- Reports BCNF anomalies

### `security-audit.php`
```bash
php scripts/security-audit.php <schema.sql>
```
Checks:
- PII columns encrypted (AES-256-GCM marked)
- Audit tables present (created_by, updated_at, action)
- Foreign key constraints (referential integrity)
- CHECK constraints (data validation)
- Unique constraints (no duplicates)

### `schema-to-diagram.php`
```bash
php scripts/schema-to-diagram.php <schema.sql> > er-diagram.mmd
```
Generates Mermaid ER diagram from SQL.

---

## 📚 Reference Documentation

See `references/` for detailed guides:

- `00-overview.md` — Architecture, workflow, progressive disclosure
- `01-requirements-gathering.md` — 8-question protocol, industry templates
- `02-normalization-rules.md` — 1NF/2NF/3NF/BCNF with examples
- `03-provider-dialects.md` — MySQL, MSSQL, PostgreSQL, MongoDB syntax
- `04-security-audit.md` — OWASP, encryption, audit trails, RLS
- `05-performance-optimization.md` — Indexing, partitioning, denormalization
- `06-schema-generation.md` — SQL dialect detection, migration strategies
- `07-coremusic-integration.md` — PHP strict_types, SOLID, .ai/ vault
- `08-examples.md` — E-commerce, CRM, social media, hospital schemas
- `09-anti-patterns.md` — Common mistakes & solutions
- `10-checklist.md` — Production-grade validation

---

## 🎬 Quick Start

**Activate skill:**
```
/skill database-normalize-maker
```

**Or use triggers:**
```
schema design
normalize this database
create sql for e-commerce
veritabanı oluştur
```

Skill will:
1. Scan `.ai/.sql/` for existing schemas
2. Ask you 8 requirement questions
3. Run deep-research on industry standards
4. Design normalized schema
5. Generate SQL + migrations + ER diagram
6. Validate against BCNF + OWASP + performance
7. Output to `.ai/.sql/` (or your chosen location)

---

## ✅ Quality Assurance

Every generated schema:
- ✅ Passes BCNF normalization test
- ✅ Passes OWASP security audit
- ✅ Includes indexes on FK, frequently queried columns
- ✅ Has audit trail tables (created_by, updated_at, action)
- ✅ Includes data dictionary (column descriptions)
- ✅ Includes ER diagram (Mermaid format)
- ✅ Includes technical report (design rationale)
- ✅ Includes migration files (up/down)
- ✅ Includes seed data (test sample)
- ✅ Compatible with PHP PDO (no raw SQL patterns)

---

## 🔗 Integration Points

Works with:
- **brainstorming skill** — Design exploration, trade-off analysis
- **deep-research skill** — Industry standards, best practices research
- **web-searcher skill** — Real-time CVE lookups, vendor docs
- **prompt-maker skill** — Requirement structuring, question generation
- **superpowers skills** — Verification, testing, documentation
- **CoreMusic .ai/ vault** — Decisions (ADRs), domains, rules

---

## 📞 Support & Advanced

For normalization deep-dives, see:
- `references/02-normalization-rules.md` — Full BCNF analysis
- `references/05-performance-optimization.md` — Denormalization trade-offs
- `references/08-examples.md` — Real-world schemas

For security hardening:
- `references/04-security-audit.md` — OWASP checklist
- `references/07-coremusic-integration.md` — PHP + CoreMusic rules

---

*Generated by skill-maker v1.0 for CoreMusic Enterprise Database Architecture*

