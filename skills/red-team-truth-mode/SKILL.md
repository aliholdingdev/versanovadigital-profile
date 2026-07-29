---
name: red-team-truth-mode
description: "Her zaman aktif — Hallüsinasyon önleme, doğrulama politikası, kaynak gösterme zorunluluğu"
version: 1.0.0
status: active
mandatory: true
triggers:
  - "her yanıt"
  - "her görev"
  - "her çıkti"
---

# Red Team • Truth Mode — HER ZAMAN AKTİF

**Bu skill her yanıtta aktif olmalıdır. Hiçbir istisna yoktur.**

## Temel Prensip

```
Bilmiyorsan söyle.
Doğrulayamıyorsan belirt.
Uydurmayı asla yapma.
```

## Yasaklanan Hallüsinasyonlar

| Hallüsinasyon Türü | Sonuç |
|---------------------|-------|
| API uydurma | Derhal sil |
| Endpoint uydurma | Doğrulanmadan kullanma |
| Sınıf uydurma | Kod yazmadan önce doğrula |
| Servis uydurma | Gerçek kodu kontrol et |
| Workflow uydurma | Mevcut workflow'ları oku |
| Mimari uydurma | ADR'leri kontrol et |

## Doğrulama Sırası

1. Vault kaynakları (index.md, brain.md, MEMORY.md)
2. İlgili dosyalar (ADR, domain spec)
3. Kaynak kodu (PHP, JS, C++)
4. Dış kaynaklar (resmi doküman)

## Doğrulama Matrisi

| Bilgi Türü | Minimum Kaynak |
|------------|----------------|
| API endpoint | routes.php + Controller |
| Sınıf varlığı | PHP dosyası |
| Servis varlığı | composer.json/package.json |
| Workflow | .workflows/ dosyası |
| Mimari karar | .ai/decisions/ ADR |

## Doğrulanamayan Bilgi

```
⚠️ VERIFICATION REQUIRED

Bu bilgi aşağıdaki kaynaklardan doğrulanamadı:
- Eksik Kanıt: [Neden doğrulanamadı]
- Muhtemel Kaynak: [Nereden bulunabilir]
- Kullanıcı Onayı: [Onay gerekiyor mu?]
```

## Dosya Yapısı Koruması

- ❌ Dosya adını değiştirme
- ❌ Klasör adını değiştirme
- ❌ Dosya silme
- ✅ İçerik iyileştirme
- ✅ Eksikleri tamamlama

## İlgili Dosyalar

- `.claude/rules/ai-development-rules.md` — AI geliştirme kuralları
- `.claude/rules/human-mode.md` — İnsansı yazım kuralları
- `.ai/brain.md` — Merkezi bilgi tabanı
