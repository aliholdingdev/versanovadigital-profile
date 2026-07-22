# skill-maker — Mimari & Progressive Disclosure

## Progressive Disclosure Sistemi

Kiro IDE skill'leri 3 katmanlı yükleme sistemi kullanır:

### Katman 1 — Discovery (~100 token)
Sadece `name` + `description` yüklenir.
Claude bu bilgiyle skill'in ne zaman aktive olacağına karar verir.

### Katman 2 — Activation
Kullanıcı isteği eşleşince `SKILL.md` tam olarak yüklenir.
Markdown gövdesi, kurallar, örnekler ve protokoller burada.

### Katman 3 — Execution
`references/` ve `scripts/` sadece gerektiğinde yüklenir.
Büyük dokümantasyon, şablonlar ve çalıştırılabilir kodlar burada.

---

## Skill Klasör Yapısı (Canonical)

```
.kiro/skills/{skill-adi}/
├── SKILL.md                    ← ZORUNLU — Kiro resmi format
├── references/                 ← Detay dokümantasyon (progressive)
│   ├── overview.md             ← Bu dosya
│   ├── rules.md                ← Format kuralları
│   ├── examples.md             ← Kullanım örnekleri
│   ├── anti-patterns.md        ← Kaçınılacak hatalar
│   ├── checklist.md            ← Kalite kontrol
│   └── coremusic-rules.md      ← Proje-spesifik kurallar
├── scripts/                    ← Çalıştırılabilir kod (opsiyonel)
│   ├── validate.py             ← Doğrulama scripti
│   └── generate.js             ← Üretim scripti
└── templates/                  ← Şablonlar (opsiyonel)
    ├── skill-template.md       ← Temel skill şablonu
    └── domain-templates/       ← Domain-spesifik şablonlar
```

---

## Skill Yaşam Döngüsü

```
Kullanıcı tetikleyici yazar
↓
Kiro: name + description yükler (Discovery)
↓
Eşleşme tespit edilir
↓
SKILL.md tam yüklenir (Activation)
↓
AI: Soru protokolü başlatır (10 soru)
↓
Cevaplar toplanır
↓
Doğrulama yapılır
↓
Klasör yapısı oluşturulur
↓
SKILL.md üretilir
↓
references/ doldurulur
↓
scripts/ doldurulur (gerekirse)
↓
templates/ doldurulur (gerekirse)
↓
Checklist doğrulanır
↓
Wiki güncellenir (log.md + brain.md)
↓
Kullanıcıya özet sunulur
```

---

## Kiro Skill Format Özeti

```yaml
---
name: skill-adi              # ZORUNLU — lowercase-hyphen, max 64 karakter
description: açıklama        # ZORUNLU — max 1024 karakter
license: MIT                 # opsiyonel
compatibility: Kiro IDE      # opsiyonel
metadata:                    # opsiyonel — tüm ek bilgiler buraya
  version: 1.0.0
  author: İsim
  category: kategori
  tags: [tag1, tag2]
---
```

**Kritik Kurallar:**
- Dosya adı: `SKILL.md` (büyük S, küçük k) — `skill.md` çalışmaz
- `name` = klasör adı (mutlaka eşleşmeli)
- `version`, `dependencies` doğrudan kullanılamaz → `metadata` altına
- `name`'de büyük harf, boşluk, alt çizgi, Türkçe karakter yasak
