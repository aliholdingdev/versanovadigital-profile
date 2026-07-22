---
name: {SKILL_ADI}
description: {SKILL_ACIKLAMASI} PHP strict_types, PDO, SOLID ve OWASP Top10:2025
  kuralları zorunlu uygulanır. Tetikleyiciler: "{TETIKLEYICI_1}", "{TETIKLEYICI_2}".
license: MIT
metadata:
  version: 1.0.0
  author: {YAZAR}
  category: backend
  tags: [php, backend, coremusic]
  coremusic_rules: enforced
  owasp: Top10:2025
  solid: enforced
---

# {SKILL_BASLIK} (PHP/Backend)

## Genel Bakış

{SKILL_DETAYLI_ACIKLAMA}

## CoreMusic PHP Zorunlu Kurallar

Bu skill aşağıdaki kuralları **otomatik** uygular:

```php
declare(strict_types=1);  // Her dosyada zorunlu
```

- PDO mandatory — raw SQL concatenation yasak
- `unserialize(user_input)` yasak
- `SELECT *` yasak — explicit column list zorunlu
- Parameterized queries zorunlu
- SOLID prensipleri zorunlu
- Constructor Injection (DI) zorunlu
- Typed Exceptions zorunlu
- Namespace: `CoreMusic\{Module}\{Class}`

## OWASP Top10:2025 Kontrolleri

- A03 Injection: SQL, Command, LDAP injection tespiti
- A07 Auth Failures: Session fixation, token güvenliği
- A01 Access Control: Auth middleware doğrulama
- A10 SSRF: URL validation kontrolü

## Mimari Katmanlar

```
Presentation  : Controller/Route — iş mantığı yok
Application   : Service/UseCase/DTO
Domain        : Entity/ValueObject/IRepository
Infrastructure: Concrete Repository, DbContext
```

## Ne Zaman Kullanılmalı?

- {TETIKLEYICI_DURUM_1}
- {TETIKLEYICI_DURUM_2}

## Çalışma Protokolü

1. PHP dosyasını/dosyalarını analiz et
2. `declare(strict_types=1)` varlığını kontrol et
3. PDO kullanımını doğrula
4. OWASP kontrolleri uygula
5. SOLID ihlallerini tespit et
6. Rapor üret

## Çıktı Formatı

```
## Analiz Raporu: {dosya_adi}

### Kritik (Hemen Düzeltilmeli)
- [ ] {KRITIK_SORUN_1}

### Orta Seviye
- [ ] {ORTA_SORUN_1}

### Öneriler
- [ ] {ONERI_1}

### Düzeltme Örnekleri
{KOD_ORNEGI}
```

## Referans Dosyalar

- `references/coremusic-rules.md` — Tam CoreMusic kuralları
- `references/examples.md` — PHP skill örnekleri
