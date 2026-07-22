# Reference 07 — CoreMusic Integration Rules

## PHP Integration (Strict Types & Security)

### PHPUnit Test Format (CoreMusic Standard)

**All generated schemas must be testable with PHP:**

```php
<?php
declare(strict_types=1);

namespace CoreMusic\Test\Unit\Database;

use PHPUnit\Framework\TestCase;
use PDO;

class NormalizationTest extends TestCase
{
    private PDO $pdo;
    
    protected function setUp(): void
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=coremusic_test', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function testUserTableExists(): void
    {
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'users'");
        $this->assertNotEmpty($stmt->fetchAll());
    }
    
    public function testUsersPrimaryKeyExists(): void
    {
        $stmt = $this->pdo->query("SHOW KEYS FROM users WHERE Key_name = 'PRIMARY'");
        $this->assertNotEmpty($stmt->fetchAll());
    }
    
    public function testUsersEmailUnique(): void
    {
        $stmt = $this->pdo->query("SHOW KEYS FROM users WHERE Key_name = 'email' AND Seq_in_index = 1");
        $result = $stmt->fetchAll();
        $this->assertTrue(count($result) > 0, "Email must have UNIQUE constraint");
    }
}
```

### CoreMusic Repository Pattern

Generated schema must work with Repository pattern:

```php
<?php
declare(strict_types=1);

namespace CoreMusic\Repository;

use PDO;
use PDOStatement;

final class UserRepository
{
    public function __construct(private PDO $pdo) {}
    
    public function findById(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE user_id = :id LIMIT 1');
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (email, created_by, created_at) VALUES (:email, :created_by, :created_at)'
        );
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':created_by', $data['created_by'], PDO::PARAM_INT);
        $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));
        
        $stmt->execute();
        return (int) $this->pdo->lastInsertId();
    }
}
```

### Audit Trail Integration

Generated schema **must include audit columns**:

```php
// When creating/updating:
$data = [
    'email' => 'user@example.com',
    'created_by' => 123,  // Current user ID
    'created_at' => date('Y-m-d H:i:s')
];

// Skill ensures schema has:
// - created_by INT (FK to users.user_id)
// - created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// - updated_by INT (FK to users.user_id)
// - updated_at TIMESTAMP (with trigger)
// - deleted_by INT (FK for soft deletes)
// - deleted_at TIMESTAMP NULL
```

---

## SOLID Principles Applied to Schema

### Single Responsibility

Each table has **one reason to change**:

```sql
-- ✅ GOOD: Each table owns one responsibility
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    email VARCHAR(255)
);

CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(10,2)
);

-- ❌ BAD: One table handles multiple responsibilities
CREATE TABLE user_orders (
    user_id INT,
    email VARCHAR(255),
    order_id INT,
    amount DECIMAL(10,2)
);
```

### Open/Closed Principle

Schema extensible without modifying existing tables:

```sql
-- Add new feature without touching existing users table
CREATE TABLE user_preferences (
    user_id INT PRIMARY KEY,
    theme VARCHAR(50),
    language VARCHAR(10),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Or add new table for feature
CREATE TABLE user_addresses (
    address_id INT PRIMARY KEY,
    user_id INT,
    address_type VARCHAR(50),
    street VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
```

### Liskov Substitution

Tables can substitute each other if contract is same:

```sql
-- Customer IS-A User (inheritance pattern)
CREATE TABLE users (
    user_id INT PRIMARY KEY,
    email VARCHAR(255),
    user_type VARCHAR(50)  -- 'customer', 'admin', 'guest'
);

-- Or use composition
CREATE TABLE customer_profiles (
    user_id INT PRIMARY KEY,
    customer_since DATE,
    loyalty_points INT,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
```

### Interface Segregation

Tables segregated into minimal, focused interfaces:

```sql
-- Don't force all user data into one table
-- Segregate into focused tables
CREATE TABLE user_accounts (
    user_id INT PRIMARY KEY,
    email VARCHAR(255),
    password_hash VARCHAR(255)
);

CREATE TABLE user_profiles (
    user_id INT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    FOREIGN KEY (user_id) REFERENCES user_accounts(user_id)
);

CREATE TABLE user_addresses (
    address_id INT PRIMARY KEY,
    user_id INT,
    city VARCHAR(100),
    zip VARCHAR(10),
    FOREIGN KEY (user_id) REFERENCES user_accounts(user_id)
);
```

### Dependency Inversion

Schema depends on abstractions, not concrete implementations:

```sql
-- Use FK constraints (abstractions) instead of hardcoded IDs
CREATE TABLE orders (
    order_id INT PRIMARY KEY,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Not this (hardcoded, no integrity):
-- user_id INT (just a number, could be invalid)
```

---

## CoreMusic Vault Integration

### .ai/decisions Integration

When generating schema, Skill reads:
- `.ai/decisions/033-sql-normalization-strategy.md` (BCNF compliance)
- `.ai/decisions/034-credential-vault-normalization.md` (encryption strategy)
- `.ai/decisions/022-database-hardened-security.md` (security rules)

### .ai/domains Integration

Reads domain rules:
- `.ai/domains/auth.md` — User auth schema requirements
- `.ai/domains/music.md` — Music catalog schema
- `.ai/domains/system.md` — System tables (logs, audit)

### .ai/.sql Integration

Places generated schema in:
- `.ai/.sql/coremusic_db.sql` — Main schema
- `.ai/.sql/migrations/001_*.sql` — Migration files
- `.ai/.sql/seed/seed_*.sql` — Seed data

### .ai/log.md Integration

Appends entry:
```markdown
## [2026-06-11 14:30:00]

### Task
database-normalize-maker skill executed for CoreMusic

### Database Design
- Type: e-commerce + CRM hybrid
- Provider: MySQL 8.0
- Entities: 15 tables, 3NF normalized
- Normalization: BCNF compliance verified

### Generated Files
- .ai/.sql/coremusic_db.sql
- .ai/.sql/migrations/001_*.sql - 004_*.sql
- .ai/.sql/seed/seed_*.sql
- .ai/.sql/data-dictionary.md
- .ai/.sql/er-diagram.md
- .ai/.sql/technical-report.md
- .ai/.sql/validation-report.md

### Security Audit
✅ All PII encrypted (AES-256-GCM marked)
✅ Audit trail present (created_by, updated_at, action)
✅ Foreign keys constraints enforced
✅ OWASP Top10:2025 compliant

### Updated Wiki Pages
- .ai/decisions/NNN-*.md (new decision record)
- .ai/log.md (this entry)
```

---

## CoreMusic Configuration

### config/database.php Integration

Generated schema assumes:

```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', 3306),
    'database' => env('DB_DATABASE', 'coremusic_db'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => 'InnoDB',
],
```

### Migration Loading

CoreMusic migrations expect:
- Location: `music.coremusic.net/database/migrations/`
- Format: `YYYY_MM_DD_HHMMSS_description.php` (Laravel style)
- Or: `.ai/.sql/migrations/NNN_*.sql` (raw SQL)

---

## Testing Integration

Generated schema must work with:

### PHPUnit (Unit Tests)

```bash
cd music.coremusic.net
vendor/bin/phpunit test/Unit/Database/
```

### Playwright (E2E Tests)

Generated schema validated through browser:
```bash
npx playwright test --grep "database"
```

### Performance Tests

```bash
# Load test with generated seed data
ab -n 10000 -c 100 http://localhost:8000/api/users
```

---

## Compliance Checklist (CoreMusic)

```sql
☐ All tables have primary key (user_id, order_id, etc.)
☐ Foreign keys defined with CASCADE/RESTRICT
☐ Audit columns: created_by, created_at, updated_by, updated_at
☐ Soft delete: deleted_at column present
☐ PII columns marked for encryption
☐ UNIQUE constraints on email, username (no duplicates)
☐ CHECK constraints on status enums
☐ NOT NULL on required columns
☐ Indexes on FK columns
☐ Indexes on frequently queried columns
☐ Normalization: 3NF or BCNF
☐ No raw SQL patterns in schema
☐ Comments explaining complex relationships
☐ charset=utf8mb4 (MySQL supports emoji)
☐ Engine=InnoDB (ACID transactions)
```

---

*Last Updated: 2026-06-11 | CoreMusic Architecture*

