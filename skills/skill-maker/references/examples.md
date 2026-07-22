# skill-maker — Domain Bazlı Örnek Skill'ler

## PHP/Backend Skill Örneği

```yaml
---
name: php-security-analyzer
description: PHP dosyalarını OWASP Top10:2025 güvenlik açıklarına karşı analiz eder.
  SQL injection, XSS, CSRF, SSRF, session fixation, file upload attack tespiti yapar.
  Güvenlik raporu ve öncelikli düzeltme listesi üretir. CoreMusic PDO ve strict_types
  kurallarını doğrular. Tetikleyiciler: "güvenlik analizi", "security scan", "php analiz",
  "açık tara", "owasp kontrol", "vulnerability check".
license: MIT
metadata:
  version: 1.0.0
  author: Bayram Ali
  category: security
  tags: [php, security, owasp, analysis]
---

# PHP Security Analyzer

## Ne Yapar?
PHP kaynak kodunu OWASP Top10:2025 açıklarına karşı analiz eder.

## Zorunlu Kontroller
- declare(strict_types=1) varlığı
- PDO kullanımı (raw SQL concatenation yasak)
- unserialize(user_input) kullanımı
- XSS vektörleri (htmlspecialchars eksikliği)
- CSRF token doğrulama
- Session fixation riski
- File upload güvenliği

## Çıktı Formatı
1. Kritik açıklar (hemen düzeltilmeli)
2. Orta seviye riskler
3. Düşük seviye öneriler
4. Düzeltme kod örnekleri
```

---

## JavaScript/Frontend Skill Örneği

```yaml
---
name: js-async-analyzer
description: JavaScript/TypeScript dosyalarını async güvenliği, memory leak ve
  race condition açısından analiz eder. AbortController kullanımı, event listener
  cleanup, detached DOM leak tespiti yapar. Tetikleyiciler: "async analiz",
  "memory leak tara", "race condition kontrol", "js analiz", "fetch analiz".
metadata:
  version: 1.0.0
  category: frontend
  tags: [javascript, async, memory, analysis]
---
```

---

## CSS/UI Skill Örneği

```yaml
---
name: css-wcag-linter
description: CSS dosyalarını WCAG 2.2 AA uyumluluğu ve ITCSS metodolojisi açısından
  denetler. Renk kontrastı, touch target boyutu, focus outline, dark theme token
  kullanımı kontrol edilir. Tetikleyiciler: "css lint", "wcag kontrol",
  "erişilebilirlik", "accessibility check", "css analiz".
metadata:
  version: 1.0.0
  category: frontend
  tags: [css, wcag, accessibility, itcss]
---
```

---

## SQL/Veritabanı Skill Örneği

```yaml
---
name: sql-query-analyzer
description: SQL sorgularını N+1 problemi, injection riski, performans ve
  SELECT * kullanımı açısından analiz eder. Parameterized query eksikliği,
  index önerileri ve sorgu optimizasyonu raporu üretir. Tetikleyiciler:
  "sql analiz", "sorgu kontrol", "n+1 tara", "sql optimize", "query review".
metadata:
  version: 1.0.0
  category: database
  tags: [sql, mysql, performance, security]
---
```

---

## DevOps/CLI Skill Örneği

```yaml
---
name: cli-test-runner
description: CoreMusic CLI test scriptlerini çalıştırır ve sonuçları analiz eder.
  pagerouter_cli_test, cache_health_cli ve all-examples.php testlerini yönetir.
  Test başarısızlıklarını kategorize eder ve düzeltme önerileri sunar.
  Tetikleyiciler: "test çalıştır", "cli test", "test runner", "test analiz".
metadata:
  version: 1.0.0
  category: devops
  tags: [cli, testing, php, automation]
---
```

---

## Genel Amaçlı Skill Örneği

```yaml
---
name: code-reviewer
description: Herhangi bir dildeki kodu SOLID prensipleri, Clean Code kuralları
  ve güvenlik açısından inceler. Refactor önerileri, naming consistency ve
  architecture iyileştirmeleri sunar. Tetikleyiciler: "kod incele", "code review",
  "kod analiz", "refactor öner", "solid kontrol", "clean code".
metadata:
  version: 1.0.0
  category: general
  tags: [review, solid, clean-code, refactor]
---
```

---

## Skill Üretim Akışı Örneği (Tam)

```
Kullanıcı: "skill oluştur"

AI: S1 → Skill adı ne olacak?
Kullanıcı: "php-security-analyzer"

AI: S2 → Ne iş yapacak?
Kullanıcı: "PHP dosyalarını güvenlik açıklarına karşı analiz etsin"

AI: S3 → Hangi domain?
Kullanıcı: "a — PHP/Backend"

AI: S4 → Tetikleyiciler?
Kullanıcı: "güvenlik analizi, security scan, php analiz, açık tara"

AI: S5 → Ne üretmeli?
Kullanıcı: "f — Güvenlik taraması"

AI: S6 → Hangi dosya tipleri?
Kullanıcı: "*.php"

AI: S7 → CoreMusic kuralları?
Kullanıcı: "Evet"

AI: S8 → Script gerekiyor mu?
Kullanıcı: "d — Hayır"

AI: S9 → Başka skill'lerle birlikte?
Kullanıcı: "kiro-skill ile birlikte"

AI: S10 → Ek kısıtlamalar?
Kullanıcı: "OWASP Top10:2025 zorunlu"

→ AI üretir:
.kiro/skills/php-security-analyzer/
├── SKILL.md
├── references/
│   ├── overview.md
│   ├── rules.md
│   ├── examples.md
│   ├── anti-patterns.md
│   ├── checklist.md
│   └── coremusic-rules.md
└── templates/
    └── security-report-template.md
```
