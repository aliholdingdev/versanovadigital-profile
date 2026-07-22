# skill-maker — CoreMusic Proje Kuralları

Bu dosya, CoreMusic projesine özgü kuralları içerir.
S7 sorusuna "Evet" cevabı verildiğinde veya PHP/Backend domain seçildiğinde
bu kurallar üretilen skill'e otomatik enjekte edilir.

---

## PHP Zorunlu Kurallar

```php
// 1. Her PHP dosyasında zorunlu
declare(strict_types=1);

// 2. PDO mandatory — raw SQL concatenation yasak
// YANLIŞ:
$sql = "SELECT * FROM users WHERE id = " . $id;  // ← SQL INJECTION RİSKİ

// DOĞRU:
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);

// 3. SELECT * yasak
// YANLIŞ:
$stmt = $pdo->query('SELECT * FROM users');

// DOĞRU:
$stmt = $pdo->query('SELECT id, name, email FROM users');

// 4. unserialize(user_input) yasak
// YANLIŞ:
$data = unserialize($_POST['data']);  // ← RCE RİSKİ

// 5. Namespace zorunlu
namespace CoreMusic\System\Cache;

// 6. Constructor Injection (DI)
class UserService {
    public function __construct(
        private readonly UserRepository $repository,
        private readonly CacheService $cache
    ) {}
}
```

---

## SOLID Prensipleri

```
S — Single Responsibility: Her sınıf tek bir sorumluluğa sahip
O — Open/Closed: Genişlemeye açık, değişime kapalı
L — Liskov Substitution: Alt sınıflar üst sınıfın yerine geçebilmeli
I — Interface Segregation: Küçük, odaklı interface'ler
D — Dependency Inversion: Soyutlamalara bağımlı ol, somutlamalara değil
```

---

## Mimari Katmanlar

```
Presentation  : Controller/Route/Middleware/ViewModel — iş mantığı yok
Application   : Service/UseCase/DTO — framework bağımsız
Domain        : Entity/ValueObject/IRepository — dış bağımlılık yok
Infrastructure: Concrete Repository, DbContext, External Adapter
```

---

## Güvenlik Kuralları (OWASP Top10:2025)

```
A01 — Broken Access Control    : Auth middleware + Role validation her yerde
A02 — Cryptographic Failures   : AES-256-GCM / ChaCha20-Poly1305 zorunlu
                                  MD5/SHA-1/DES yasak
A03 — Injection                : Parameterized queries zorunlu
A04 — Insecure Design          : Fail-safe, not fail-open
A05 — Security Misconfiguration: Secure defaults
A06 — Vulnerable Components    : Pinned versions, audit logs
A07 — Auth Failures            : Secure session + token management
A08 — Software Integrity       : Dependency verification
A09 — Logging Failures         : Audit logs mandatory
A10 — SSRF                     : URL validation, allowlist
```

---

## JavaScript Kuralları

```javascript
// 1. no var — let/const kullan
const userId = 1;
let counter = 0;

// 2. async/await mandatory
async function fetchUser(id) {
    const response = await fetch(`/api/users/${id}`);
    return response.json();
}

// 3. AbortController for fetch
const controller = new AbortController();
const response = await fetch('/api/data', {
    signal: controller.signal
});
// Route değişiminde: controller.abort();

// 4. DOM-safe rendering — no unsafe innerHTML
// YANLIŞ:
element.innerHTML = userInput;  // ← XSS RİSKİ

// DOĞRU:
element.textContent = userInput;
// veya DOMPurify kullan

// 5. Event listener cleanup
function cleanup() {
    element.removeEventListener('click', handler);
}
```

---

## CSS Kuralları

```css
/* 1. CSS custom properties zorunlu */
:root {
    --cm-primary: #1a1a2e;
    --cm-accent: #e94560;
    --cm-text: #ffffff;
}

/* 2. WCAG 2.2 AA — minimum kontrast 4.5:1 */
.button {
    color: var(--cm-text);
    background: var(--cm-primary);
    /* Kontrast oranı: 7.2:1 ✅ */
}

/* 3. Touch target minimum 24×24px */
.icon-button {
    min-width: 24px;
    min-height: 24px;
    padding: 8px;
}

/* 4. Focus outline zorunlu */
:focus-visible {
    outline: 3px solid var(--cm-accent);
    outline-offset: 2px;
}
```

---

## SQL Kuralları

```sql
-- 1. Parameterized queries zorunlu
-- YANLIŞ:
SELECT * FROM users WHERE id = {$id}  -- SQL INJECTION

-- DOĞRU (PDO):
SELECT id, name, email FROM users WHERE id = ?

-- 2. SELECT * yasak
-- YANLIŞ:
SELECT * FROM albums

-- DOĞRU:
SELECT id, title, artist_id, release_year FROM albums

-- 3. N+1 problemi önleme — JOIN kullan
-- YANLIŞ (N+1):
SELECT id FROM albums;
-- Her album için: SELECT * FROM tracks WHERE album_id = ?

-- DOĞRU:
SELECT a.id, a.title, t.id as track_id, t.name
FROM albums a
LEFT JOIN tracks t ON t.album_id = a.id
```

---

## Cache Namespace

```php
// CoreMusic cache namespace
use CoreMusic\System\Cache\CacheService;
use CoreMusic\System\Cache\ApcuAdapter;
use CoreMusic\System\Cache\ChainAdapter;

// Cache key format
$key = 'coremusic:module:entity:id';
```

---

## Exception Handling

```php
// Severity: sadece E_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR → exception
// E_NOTICE, E_WARNING → log-only (production'da istek kırılmasın)

// Typed exceptions zorunlu
class UserNotFoundException extends DomainException {}
class InvalidCredentialsException extends SecurityException {}
```
