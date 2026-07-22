# skill-maker — Production-Grade Kalite Kontrol Listesi

## Üretim Öncesi Kontrol

```
[ ] Kullanıcıya 10 soru soruldu mu?
[ ] Tüm sorular yanıtlandı mı?
[ ] Domain belirlendi mi?
[ ] CoreMusic kuralları gerekip gerekmediği soruldu mu?
[ ] Tetikleyiciler belirlendi mi?
```

---

## SKILL.md Kontrol

```
[ ] Dosya adı: SKILL.md (büyük S, küçük k)
[ ] name: sadece lowercase-hyphen
[ ] name: max 64 karakter
[ ] name: klasör adıyla eşleşiyor
[ ] name: mevcut skill'lerle çakışmıyor
[ ] description: max 1024 karakter
[ ] description: ne zaman aktive olacağı net
[ ] description: tetikleyiciler açıkça belirtilmiş
[ ] description: hangi dosya tipleriyle çalıştığı belirtilmiş
[ ] YAML: sadece izin verilen alanlar (name, description, license, metadata)
[ ] YAML: version metadata altında
[ ] YAML: dependencies metadata altında
[ ] metadata: version mevcut
[ ] metadata: author mevcut
[ ] metadata: category mevcut
```

---

## Klasör Yapısı Kontrol

```
[ ] .kiro/skills/{skill-adi}/ klasörü oluşturuldu
[ ] SKILL.md mevcut
[ ] references/ klasörü mevcut
[ ] references/overview.md mevcut
[ ] references/rules.md mevcut
[ ] references/examples.md mevcut
[ ] references/anti-patterns.md mevcut
[ ] references/checklist.md mevcut
[ ] scripts/ klasörü oluşturuldu (gerekirse)
[ ] templates/ klasörü oluşturuldu (gerekirse)
```

---

## CoreMusic Kuralları Kontrol (PHP Domain)

```
[ ] declare(strict_types=1) kuralı eklendi
[ ] PDO mandatory kuralı eklendi
[ ] unserialize(user_input) yasak kuralı eklendi
[ ] SOLID prensipleri kuralı eklendi
[ ] XSS mitigation kuralı eklendi
[ ] CSRF protection kuralı eklendi
[ ] SQL injection prevention kuralı eklendi
[ ] SSRF mitigation kuralı eklendi
[ ] Session fixation prevention kuralı eklendi
```

---

## CoreMusic Kuralları Kontrol (JS Domain)

```
[ ] no var kuralı eklendi
[ ] async/await mandatory kuralı eklendi
[ ] AbortController kuralı eklendi
[ ] DOM-safe rendering kuralı eklendi
[ ] Event listener cleanup kuralı eklendi
[ ] CSP compatibility kuralı eklendi
```

---

## CoreMusic Kuralları Kontrol (CSS Domain)

```
[ ] WCAG 2.2 AA kuralı eklendi
[ ] CSS custom properties (var(--cm-*)) kuralı eklendi
[ ] Touch target 24×24px kuralı eklendi
[ ] Dark theme tokens kuralı eklendi
[ ] ITCSS methodology kuralı eklendi
```

---

## Güvenlik Kontrol

```
[ ] Script'lerde API key/şifre sabit kodlanmadı
[ ] External servis erişimi MCP üzerinden
[ ] Kullanıcı girdileri validate ediliyor
[ ] Sandbox-aware yazıldı
```

---

## Wiki Güncelleme Kontrol

```
[ ] .ai/log.md append edildi
[ ] Log formatı doğru (timestamp, task, dosyalar, domain, kurallar)
[ ] .ai/brain.md güncellendi (gerekirse)
[ ] Yeni ADR oluşturuldu (mimari karar varsa)
```

---

## Son Kontrol

```
[ ] Tüm yukarıdaki maddeler geçti
[ ] Skill Kiro IDE'de test edildi (mümkünse)
[ ] Kullanıcıya özet sunuldu
[ ] Üretilen dosyaların listesi gösterildi
```
