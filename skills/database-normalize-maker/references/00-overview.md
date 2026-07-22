# Reference 00 — Skill Overview & Architecture

## Progressive Disclosure

This skill is designed for **progressive disclosure**: start simple, deepen as needed.

### Level 1: Quick Start (5 min)
- Use triggers: `schema`, `sql`, `normalize`, `database`
- Skill asks 8 questions
- Generates schema.sql

### Level 2: Detailed Design (30 min)
- Review generated ER diagram
- Understand normalization decisions (BCNF analysis)
- Review security audit report
- Approve design before generation

### Level 3: Deep Dive (2+ hours)
- Study references (normalization rules, provider dialects, examples)
- Understand performance trade-offs
- Analyze existing schemas
- Make custom denormalization decisions

---

## Architecture Overview

```
┌────────────────────────────────────────────────┐
│ Phase 1: Project Analysis (Silent)             │
│ Scan .ai/.sql/, .ai/brain.md, .ai/decisions/  │
└────────────────────────────────────────────────┘
                        ↓
┌────────────────────────────────────────────────┐
│ Phase 2: Requirement Gathering (8 Questions)   │
│ User input: industry, scale, security, perf    │
└────────────────────────────────────────────────┘
                        ↓
┌────────────────────────────────────────────────┐
│ Phase 3: Research (deep-research auto-trigger) │
│ Industry standards, vendor best practices      │
└────────────────────────────────────────────────┘
                        ↓
┌────────────────────────────────────────────────┐
│ Phase 4: Design (Interactive Review)           │
│ ER diagram, normalization assessment, security │
└────────────────────────────────────────────────┘
                        ↓
┌────────────────────────────────────────────────┐
│ Phase 5: Generation (SQL, migrations, docs)    │
│ Provider-specific output to .ai/.sql/          │
└────────────────────────────────────────────────┘
                        ↓
┌────────────────────────────────────────────────┐
│ Phase 6: Validation (Audit Scripts)            │
│ BCNF, OWASP, performance, CoreMusic rules      │
└────────────────────────────────────────────────┘
```

---

## Skill Inputs & Outputs

### Inputs
- **Project directory** (scanned automatically)
- **8 requirement answers** (interactive)
- **Approval of design** (before generation)

### Outputs
- `{project}-schema.sql` — Main schema (provider-specific)
- `{project}-migrations/` — Up/down migration files
- `{project}-seed.sql` — Test data
- `{project}-data-dictionary.md` — Column descriptions
- `{project}-er-diagram.md` — Mermaid ER visualization
- `{project}-technical-report.md` — Architecture rationale
- `{project}-validation-report.md` — BCNF + OWASP audit

### Integration
- Works with `.ai/` vault (reads decisions, domains, rules)
- Triggers deep-research (industry standards)
- Generates PDF-ready documentation
- PHP validation scripts (normalization, security audit)

---

## When to Use This Skill

✅ **Use when:**
- Starting new project database design
- Auditing existing schema (preservation + analysis)
- Migrating to new database provider (MySQL → PostgreSQL)
- Scaling existing database (performance tuning, partitioning)
- Compliance audit (encryption, audit trails, RLS)
- Integrating with legacy database (reverse-engineer schema)

❌ **Don't use for:**
- Simple data cleanup (use MySQL directly)
- One-off queries (use `sql` trigger on query analyzer)
- Real-time streaming (use event-sourcing specialist)

---

## Key Concepts

### Normalization Forms

| Form | Guarantee | Example Violation |
|------|-----------|------------------|
| **1NF** | No repeating groups | Table with `tags TEXT` instead of separate table |
| **2NF** | No partial key dependencies | Table: (student_id, course_id, professor) where prof depends on course only |
| **3NF** | No transitive dependencies | Table: (student_id, city_id, city_name) where city_name transitive |
| **BCNF** | Every determinant is candidate key | Table: (subject, professor, time) where prof → time but not composite key |

### Security Concepts

- **Encryption at-rest** — AES-256-GCM for PII columns
- **Audit trail** — created_by, created_at, updated_at, action columns
- **Row-Level Security (RLS)** — PostgreSQL, MSSQL support tenant isolation
- **Column masking** — Hide PII in dev/test environments
- **Parameterized queries** — Always use prepared statements

### Performance Concepts

- **Covering index** — All columns in query are in index (no table access)
- **Partial index** — Index only rows matching WHERE condition
- **Denormalization** — Intentional redundancy for read performance
- **Partitioning** — Split large table across physical storage
- **Archiving** — Move old data to separate table/database

---

## Workflow States

### State 1: Analyzing Existing Schema
If project has existing `.sql` files:
- Skill loads and parses schema
- Checks normalization level
- Identifies security gaps
- Proposes improvements

### State 2: Green-Field Design
If no schema exists:
- Skill asks questions
- Runs research
- Designs from scratch

### State 3: Hybrid (Preserve + Redesign)
- Some tables preserved as-is
- Other tables redesigned
- Careful migration strategy

---

## Examples (Quick Reference)

| Industry | Primary Entities | Normalization Challenge |
|----------|------------------|------------------------|
| **E-Commerce** | Product, Order, Customer, Inventory | SKU variants, pricing tiers |
| **CRM** | Contact, Account, Opportunity, Activity | Contact roles, relationship histories |
| **Hospital** | Patient, Doctor, Visit, Prescription | Multiple addresses, insurance details |
| **Social Media** | User, Post, Comment, Like, Follower | Denormalize for feed performance |
| **ERP** | Purchase, Invoice, Payment, Allocation | Multi-currency, multi-plant |

See `08-examples.md` for full schemas.

---

## Troubleshooting

**Q: Skill asks same questions repeatedly?**
A: Clear session cache. Skill should remember past answers.

**Q: Generated schema doesn't match my expectations?**
A: Review "Design Phase" output before generation. Skill creates visualization for approval.

**Q: How do I handle denormalization for performance?**
A: See `05-performance-optimization.md`. Skill documents every denormalization decision with rationale.

**Q: How do I ensure security compliance?**
A: See `04-security-audit.md`. Skill validates against OWASP checklist automatically.

**Q: Can I modify generated schema?**
A: Yes! Generated schemas are starting points. Skill documents design decisions so you can adjust.

---

## Next Steps

1. Read `01-requirements-gathering.md` for detailed Q&A protocol
2. Read `02-normalization-rules.md` for BCNF deep-dive
3. Read `08-examples.md` for your industry schema
4. Try activating skill with a trigger
5. Answer 8 questions
6. Review generated design & ER diagram
7. Approve or iterate

---

*Last Updated: 2026-06-11 | Skill Version: 1.0 | CoreMusic Enterprise*

