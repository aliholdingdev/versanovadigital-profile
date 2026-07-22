---
name: skill-maker
description: Yeni Kiro skill'leri oluşturmak için interaktif rehber. Kullanıcıya sorular sorarak domain, kapsam ve gereksinimler belirlenir; SKILL.md + references/ + scripts/ + templates/ tam klasör yapısıyla production-grade skill üretilir. CoreMusic PHP/JS standartları, OWASP, SOLID ve Kiro resmi formatı zorunlu uygulanır. Tetikleyiciler: "skill oluştur", "skill maker", "beceri oluştur", "beceri maker", "yeni skill", "skill yap", "create skill".
license: MIT
metadata:
  version: 1.0.0
  author: Bayram Ali (ULTRATHINK Engineering)
  compatibility: Kiro IDE
  coremusic_rules: enforced
  owasp: Top10:2025
  solid: enforced
  last_updated: 2026-05-29
  category: meta-skill
  tags: [skill-builder, kiro, meta, generator, interactive]
---

# skill-maker — Kiro Skill Üretici

## Genel Bakış

Bu skill, Kiro IDE için yeni skill'ler oluşturmak amacıyla tasarlanmış bir **meta-skill**'dir.
Kullanıcıya sistematik sorular sorarak gereksinimler toplanır, ardından production-grade,
tam klasör yapısına sahip bir skill paketi otomatik üretilir.

**Üretilen çıktı her zaman şunları içerir:**
- `SKILL.md` — Kiro resmi formatında, doğrulanmış YAML frontmatter ile
- `references/` — Progressive disclosure için detay dokümantasyonu
- `scripts/` — Deterministik görevler için çalıştırılabilir scriptler (gerekirse)
- `templates/` — Yeniden kullanılabilir şablonlar (gerekirse)

---

## Aktivasyon Koşulları

Bu skill aşağıdaki tetikleyicilerden **herhangi biri** kullanıldığında aktive olur:

```
skill oluştur       skill maker         skill yap
skill üret          yeni skill          create skill
beceri oluştur      beceri maker        beceri yap
beceri üret         yeni beceri         skill builder
skill-maker         beceri-maker        skill factory
```

---

## Zorunlu Çalışma Protokolü

### ADIM 1 — Bağlam Keşfi (Sessiz)

Skill aktive olduğunda AI otomatik olarak şunları analiz eder:
- `.kiro/skills/` dizinindeki mevcut skill'ler (çakışma kontrolü)
- `AGENTS.md` ve steering dosyaları (proje kuralları)
- `.ai/brain.md` ve `.ai/log.md` (proje bağlamı)

### ADIM 2 — Zorunlu Soru Protokolü (10 Soru)

AI kullanıcıya **sırayla** şu soruları sormalıdır. Her soru bağımsız, net ve kısa olmalıdır.
Kullanıcı cevaplamadan bir sonraki adıma geçilemez.

```
S1. Bu skill'in adı ne olacak?
    (Kural: sadece küçük harf, sayı, tire — örn: "php-analyzer", "css-linter")

S2. Bu skill ne iş yapacak? (1-3 cümle ile açıkla)

S3. Hangi domain için? 
    (a) PHP/Backend  (b) JavaScript/Frontend  (c) CSS/UI  (d) DevOps/CLI
    (e) Güvenlik/Security  (f) Test/QA  (g) Veritabanı/SQL  (h) Genel/Diğer

S4. Bu skill hangi kelimelerle tetiklenmeli?
    (Virgülle ayır — örn: "analiz et, incele, kontrol et")

S5. Skill çalıştığında ne üretmeli?
    (a) Analiz/Rapor  (b) Kod üretimi  (c) Refactor önerisi  (d) Dokümantasyon
    (e) Test üretimi  (f) Güvenlik taraması  (g) Hepsi  (h) Diğer

S6. Hangi dosya tipleriyle çalışacak?
    (örn: *.php, *.js, *.ts, *.css, *.sql — veya "hepsi")

S7. CoreMusic proje kuralları uygulanacak mı?
    (PHP strict_types, SOLID, OWASP Top10:2025, PDO zorunlu, vb.)

S8. Çalıştırılabilir script gerekiyor mu?
    (a) Evet — Python  (b) Evet — Node.js  (c) Evet — PHP  (d) Hayır

S9. Bu skill başka skill'lerle birlikte çalışacak mı?
    (Varsa hangileri? — örn: "kiro-skill ile birlikte")

S10. Ek kısıtlamalar veya özel gereksinimler var mı?
     (Güvenlik, gizlilik, performans, vb.)
```

### ADIM 3 — Doğrulama & Üretim

Tüm sorular yanıtlandıktan sonra AI şunları yapar:

1. **Name validation** — lowercase-hyphen, max 64 karakter, mevcut skill'lerle çakışma yok
2. **Description validation** — max 1024 karakter, tetikleyiciler açık
3. **Domain rules injection** — CoreMusic kuralları gerekiyorsa otomatik eklenir
4. **Klasör yapısı oluşturma** — `.kiro/skills/{skill-adi}/` altında tam yapı
5. **SKILL.md üretimi** — Kiro resmi formatında, doğrulanmış
6. **references/ doldurma** — Domain-spesifik detay dokümantasyonu
7. **scripts/ doldurma** — Gerekirse çalıştırılabilir scriptler
8. **templates/ doldurma** — Gerekirse şablonlar
9. **Wiki güncelleme** — `.ai/log.md` append, `.ai/brain.md` güncelleme
10. **Checklist doğrulama** — Tüm zorunlu alanlar kontrol edilir

### ADIM 4 — Kalite Kontrol Checklist

```
[ ] name: sadece lowercase-hyphen, max 64 karakter
[ ] name: klasör adıyla eşleşiyor
[ ] name: mevcut skill'lerle çakışmıyor
[ ] description: max 1024 karakter
[ ] description: tetikleyiciler açıkça belirtilmiş
[ ] description: ne zaman aktive olacağı net
[ ] SKILL.md: büyük S, küçük k (SKILL.md)
[ ] YAML frontmatter: sadece izin verilen alanlar
[ ] metadata: version, author, category mevcut
[ ] CoreMusic kuralları: gerekiyorsa enjekte edildi
[ ] OWASP: güvenlik kuralları eklendi (gerekirse)
[ ] SOLID: mimari kurallar eklendi (gerekirse)
[ ] references/: en az 1 referans dosyası
[ ] Wiki: log.md append edildi
[ ] Wiki: brain.md güncellendi (gerekirse)
```

---

## CoreMusic Zorunlu Kurallar (Otomatik Enjeksiyon)

Kullanıcı S7'de "Evet" derse veya PHP/Backend domain seçilirse, üretilen skill'e
aşağıdaki kurallar **otomatik** eklenir:

### PHP Kuralları
```
- declare(strict_types=1) zorunlu
- PDO mandatory, raw SQL concatenation yasak
- unserialize(user_input) yasak
- SOLID prensipleri zorunlu
- Hexagonal Architecture awareness
- Repository Pattern + Service Layer
- Constructor Injection (DI)
- Typed Exceptions
```

### Güvenlik Kuralları (OWASP Top10:2025)
```
- XSS mitigations zorunlu
- CSRF protection zorunlu
- SQL Injection prevention (parameterized queries)
- SSRF mitigation
- Session fixation prevention
- File upload attack prevention
- Header injection prevention
- RCE vector analysis
```

### JavaScript Kuralları
```
- no var, no any (TypeScript)
- async/await mandatory
- AbortController for fetch
- DOM-safe rendering (no unsafe innerHTML)
- CSP compatibility
- Event listener cleanup
```

### CSS/UI Kuralları
```
- WCAG 2.2 AA minimum
- CSS custom properties (var(--cm-*))
- Touch target 24×24px minimum
- Dark theme tokens
- ITCSS methodology
```

---

## Üretilen Skill Yapısı

```
.kiro/skills/{skill-adi}/
├── SKILL.md                    ← Ana skill dosyası (Kiro resmi format)
├── references/
│   ├── overview.md             ← Genel bakış ve mimari
│   ├── rules.md                ← Domain-spesifik kurallar
│   ├── examples.md             ← Kullanım örnekleri
│   ├── anti-patterns.md        ← Kaçınılacak hatalar
│   └── checklist.md            ← Kalite kontrol listesi
├── scripts/
│   └── (gerekirse çalıştırılabilir scriptler)
└── templates/
    └── (gerekirse şablonlar)
```

---

## Örnek Kullanım Senaryoları

### Senaryo 1: PHP Güvenlik Analiz Skill'i

```
Kullanıcı: "skill oluştur"
AI: S1 sorusu → "php-security-analyzer"
AI: S2 sorusu → "PHP dosyalarını OWASP Top10 açıklarına karşı analiz eder"
...
Çıktı: .kiro/skills/php-security-analyzer/ (tam yapı)
```

### Senaryo 2: CSS Lint Skill'i

```
Kullanıcı: "beceri maker"
AI: S1 sorusu → "css-linter"
AI: S2 sorusu → "CSS dosyalarını ITCSS ve WCAG 2.2 kurallarına göre denetler"
...
Çıktı: .kiro/skills/css-linter/ (tam yapı)
```

### Senaryo 3: SQL Analiz Skill'i

```
Kullanıcı: "yeni skill"
AI: S1 sorusu → "sql-query-analyzer"
AI: S2 sorusu → "SQL sorgularını N+1, injection ve performans açısından analiz eder"
...
Çıktı: .kiro/skills/sql-query-analyzer/ (tam yapı)
```

---

## Güvenlik Notları

- Üretilen skill'lere **asla** API anahtarı, şifre veya secret sabit kodlanmaz
- Tüm external servis erişimleri MCP bağlantıları üzerinden yapılır
- Üretilen scriptler sandbox-aware olarak yazılır
- Kullanıcı girdileri her zaman validate edilir

---

## Wiki Güncelleme Protokolü

Her skill üretiminden sonra AI otomatik olarak şunları yapar:

```markdown
## [YYYY-MM-DD HH:mm:ss]

### Task
skill-maker ile yeni skill üretildi: {skill-adi}

### Üretilen Dosyalar
- .kiro/skills/{skill-adi}/SKILL.md
- .kiro/skills/{skill-adi}/references/overview.md
- .kiro/skills/{skill-adi}/references/rules.md
- .kiro/skills/{skill-adi}/references/examples.md
- .kiro/skills/{skill-adi}/references/anti-patterns.md
- .kiro/skills/{skill-adi}/references/checklist.md

### Domain
{seçilen domain}

### CoreMusic Kuralları
{uygulandı / uygulanmadı}

### Kalite Kontrol
Tüm checklist maddeleri geçti: ✅
```

---

## Sınırlamalar

- Üretilen skill'ler Kiro IDE formatındadır; diğer IDE'lere uyarlama gerekebilir
- Script'ler çalışma zamanında bağımlılık gerektirebilir (PyPI/npm)
- API Skills için runtime'da ek paket yüklenemez; bağımlılıklar önceden yüklü olmalı
- Çok büyük referans dosyaları için progressive disclosure kullanılır

---

## Referans Dosyalar

Detaylı kurallar ve örnekler için `references/` klasörüne bakın:

- `references/overview.md` — Skill mimarisi ve progressive disclosure
- `references/rules.md` — Kiro resmi format kuralları (tam)
- `references/examples.md` — Domain bazlı örnek skill'ler
- `references/anti-patterns.md` — Yaygın hatalar ve çözümleri
- `references/checklist.md` — Production-grade kalite kontrol listesi
- `references/coremusic-rules.md` — CoreMusic proje kuralları (PHP, JS, CSS, SQL)
