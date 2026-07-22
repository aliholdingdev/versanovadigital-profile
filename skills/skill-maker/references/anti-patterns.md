# skill-maker — Yaygın Hatalar ve Çözümleri

## HATA 1: Geçersiz YAML Frontmatter Alanı

**Hata:**
```
unexpected key in SKILL.md frontmatter: properties must be in
('name', 'description', 'license', 'allowed-tools', 'metadata')
```

**Neden olur:**
```yaml
---
name: skill-adi
version: 1.0.0        ← YANLIŞ — doğrudan kullanılamaz
dependencies: ...     ← YANLIŞ — doğrudan kullanılamaz
---
```

**Çözüm:**
```yaml
---
name: skill-adi
metadata:
  version: 1.0.0
  dependencies: python>=3.8
---
```

---

## HATA 2: Geçersiz Skill Adı

**Hata:**
```
Skill name can only contain lowercase letters, numbers, and hyphens
```

**Örnekler:**
```yaml
❌ name: PHP Güvenlik Analizi    → büyük harf + boşluk
❌ name: php_security_analyzer   → alt çizgi
❌ name: PHPSecurityAnalyzer     → büyük harf
❌ name: php.security.analyzer   → nokta
❌ name: php-güvenlik-analizi    → Türkçe karakter
```

**Çözüm:**
```yaml
✅ name: php-guvenlik-analizi
✅ name: php-security-analyzer
```

---

## HATA 3: Yanlış Dosya Adı

**Hata:**
```
SKILL.md not found
```

**Neden olur:**
```
❌ skill.md     → küçük s
❌ Skill.md     → büyük S ama küçük k değil (bu aslında doğru)
❌ SKILL.MD     → büyük MD
❌ skill.MD     → karışık
```

**Çözüm:**
```
✅ SKILL.md     → büyük S, küçük k, küçük d
```

---

## HATA 4: Name ≠ Klasör Adı

**Hata:** Skill yüklenmiyor veya tanınmıyor.

**Neden olur:**
```
.kiro/skills/my-skill/SKILL.md
---
name: different-name    ← YANLIŞ — klasör adıyla eşleşmiyor
---
```

**Çözüm:**
```
.kiro/skills/php-analyzer/SKILL.md
---
name: php-analyzer      ← DOĞRU — klasör adıyla eşleşiyor
---
```

---

## HATA 5: Çok Kısa Description

**Sorun:** Claude skill'i ne zaman kullanacağını bilemiyor.

```yaml
❌ description: PHP analiz eder.
```

**Çözüm:**
```yaml
✅ description: PHP dosyalarını OWASP Top10:2025 güvenlik açıklarına karşı analiz
  eder. SQL injection, XSS, CSRF tespiti yapar. Tetikleyiciler: "güvenlik analizi",
  "security scan", "php analiz", "açık tara".
```

---

## HATA 6: Tetikleyiciler Belirtilmemiş

**Sorun:** Skill hiç aktive olmuyor.

**Çözüm:** Description'a tetikleyici kelimeleri ekle:
```yaml
description: ... Tetikleyiciler: "analiz et", "incele", "kontrol et", "tara".
```

---

## HATA 7: ZIP İçinde ZIP

**Hata:**
```
Zip cannot contain nested zip files
```

**Çözüm:** ZIP içinde başka ZIP dosyası olmamalı.

---

## HATA 8: CoreMusic Kuralları Eksik

**Sorun:** PHP skill üretildi ama `strict_types`, PDO kuralları yok.

**Çözüm:** S7 sorusuna "Evet" cevabı verildiğinde veya PHP domain seçildiğinde
CoreMusic kuralları otomatik enjekte edilir. Manuel kontrol:

```php
// Her PHP dosyasında zorunlu
declare(strict_types=1);

// PDO zorunlu — raw SQL yasak
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);

// unserialize(user_input) yasak
// $data = unserialize($_POST['data']); ← YASAK
```

---

## HATA 9: Güvenlik Açığı İçeren Script

**Sorun:** Script'e API key veya şifre sabit kodlandı.

```python
# YANLIŞ
API_KEY = "sk-1234567890abcdef"  ← YASAK
```

**Çözüm:**
```python
# DOĞRU
import os
API_KEY = os.environ.get('API_KEY')
if not API_KEY:
    raise ValueError("API_KEY environment variable not set")
```

---

## HATA 10: Wiki Güncellenmedi

**Sorun:** Skill üretildi ama `log.md` append edilmedi.

**Çözüm:** Her skill üretiminden sonra `.ai/log.md` otomatik append edilmeli.
Bu skill-maker'ın zorunlu son adımıdır.
