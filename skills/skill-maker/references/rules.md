# skill-maker — Kiro Resmi Format Kuralları (Tam Referans)

## YAML Frontmatter Kuralları

### İzin Verilen Alanlar

```yaml
---
name: string          # ZORUNLU
description: string   # ZORUNLU
license: string       # opsiyonel
compatibility: string # opsiyonel
metadata:             # opsiyonel — tüm ek bilgiler buraya
  version: string
  author: string
  dependencies: string
  category: string
  tags: [...]
  custom_field: any
---
```

### Yasak Kullanım

```yaml
# YANLIŞ — version doğrudan kullanılamaz
---
name: skill-adi
version: 1.0.0        ← HATA
dependencies: ...     ← HATA
---

# DOĞRU
---
name: skill-adi
metadata:
  version: 1.0.0
  dependencies: ...
---
```

---

## Name Kuralları

| Kural | Durum |
|-------|-------|
| Sadece küçük harf | ✅ ZORUNLU |
| Sayılar | ✅ İzin verilir |
| Tire (-) | ✅ İzin verilir |
| Büyük harf | ❌ YASAK |
| Boşluk | ❌ YASAK |
| Alt çizgi (_) | ❌ YASAK |
| Türkçe karakter (ı,ş,ğ,ü,ö,ç) | ❌ YASAK |
| Özel karakterler (@, #, !, .) | ❌ YASAK |
| Max 64 karakter | ✅ ZORUNLU |
| Klasör adıyla eşleşme | ✅ ZORUNLU |

### Türkçe → İngilizce Dönüşüm

| Türkçe | İngilizce |
|--------|-----------|
| ı | i |
| ş | s |
| ğ | g |
| ü | u |
| ö | o |
| ç | c |

**Örnekler:**
- "PHP Güvenlik Analizi" → `php-guvenlik-analizi`
- "Müşteri Segmentasyon" → `musteri-segmentasyon`
- "Görselleştirme Uzmanı" → `gorsellestirme-uzmani`

---

## Description Kuralları

- Max **1024 karakter** (Kiro resmi limiti)
- Ne zaman aktive olacağını açıkça belirt
- Tetikleyici kelimeleri dahil et
- Hangi dosya tipleriyle çalıştığını belirt
- Hangi çıktıları ürettiğini belirt

### İyi Description Örneği

```yaml
description: PHP dosyalarını OWASP Top10:2025 güvenlik açıklarına karşı analiz eder.
  SQL injection, XSS, CSRF, SSRF, session fixation tespiti yapar. Güvenlik raporu
  ve düzeltme önerileri üretir. Tetikleyiciler: "güvenlik analizi", "security scan",
  "php analiz", "açık tara", "vulnerability check".
```

### Kötü Description Örneği

```yaml
description: PHP analiz eder.  # Çok kısa, belirsiz
```

---

## Dosya Adı Kuralları

```
✅ SKILL.md    ← Doğru (büyük S, küçük k)
❌ skill.md    ← Yanlış (küçük s)
❌ Skill.MD    ← Yanlış (büyük MD)
❌ SKILL.MD    ← Yanlış (büyük MD)
```

---

## ZIP Paketleme Kuralları (agentskills.io için)

```
skill-adi.zip
└── skill-adi/          ← Klasör adı ZIP adıyla eşleşmeli
    ├── SKILL.md
    ├── references/
    ├── scripts/
    └── templates/
```

**Yasak:**
- ZIP içinde başka ZIP
- Dosyalar doğrudan ZIP kökünde
- `.DS_Store`, `__pycache__`, `.git/`, `node_modules/`

---

## Kiro Skill vs Claude.ai Skill Farkları

| Özellik | Kiro IDE | Claude.ai |
|---------|----------|-----------|
| Dosya adı | `SKILL.md` | `Skill.md` |
| name max | 64 karakter | 64 karakter |
| description max | 1024 karakter | 200 karakter |
| metadata | `metadata:` altında | `metadata:` altında |
| version field | `metadata.version` | `metadata.version` |
| Klasör yapısı | Zorunlu | Zorunlu |
| ZIP format | Gerekli (upload için) | Gerekli (upload için) |

**Not:** Bu skill Kiro IDE için optimize edilmiştir.
