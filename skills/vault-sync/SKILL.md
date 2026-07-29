---
name: vault-sync
description: "ZORUNLU — Her vault değişikliği sonrası kullanılır. Dosya indeksini, referansları ve bütünlüğü senkronize eder."
version: 1.0.0
status: active
mandatory: true
triggers:
  - "vault güncelle"
  - "senkronize et"
  - "vault sync"
  - "dosyaları güncelle"
  - "indeksi güncelle"
  - "bütünlük kontrol et"
  - "her değişiklik sonrası"
---

# Vault Sync Skill — ZORUNLU KULLANIM

**Bu skill her vault değişikliği sonrasında zorunlu olarak kullanılmalıdır. Hiçbir istisna yoktur.**

## Kullanım Akışı

### Adım 1: Tarama
- Vault dosyalarını listele
- Değişiklikleri tespit et (git diff)
- Yeni dosyaları belirle

### Adım 2: İndeksleme
- `.ai/index.md` güncelle
- Yeni dosyaları ekle
- Silinen dosyaları çıkar

### Adım 3: Referans Doğrulama
- Tüm `[[wiki-link]]`'leri kontrol et
- Kırık linkleri bul
- Dairesel referansları kontrol et

### Adım 4: Bütünlük Kontrolü
- Checksum'ları hesapla
- Önceki checksum'larla karşılaştır
- Bozulma tespit et

### Adım 5: Raporlama
- Güncelleme raporu oluştur
- `.ai/log.md`'ye kaydet
- Kullanıcıya bildir

## Otomatik Tetikleyiciler

| Tetikleme | Aksiyon | Öncelik |
|-----------|---------|---------|
| `git commit` sonrası | Vault'u tara | Düşük |
| `git merge` sonrası | Bütünlüğü doğrula | Yüksek |
| Yeni dosya oluşturulması | İndekse ekle | Yüksek |
| Dosya silinmesi | İndeksten çıkar | Yüksek |
| Deployment sonrası | Vault'u güncelle | Kritik |

## Kontrol Listesi

- [ ] Tüm vault dosyaları tarandı
- [ ] Yeni dosyalar indekslendi
- [ ] Silinen dosyalar çıkarıldı
- [ ] Referanslar kontrol edildi
- [ ] Bütünlük doğrulandı
- [ ] Rapor oluşturuldu
- [ ] Log güncellendi

## Hata Yönetimi

| Hata | Aksiyon |
|------|---------|
| Dosya okunamıyor | İzinleri kontrol et |
| Referans bulunamadı | Düzelt veya kaldır |
| Bütünlük hatası | Yedekten geri yükle |
| Güncelleme başarısız | Manuel müdahale gerekli |

## Komutlar

```powershell
# Tam senkronizasyon
.\.ai\scripts\vault-sync.ps1 -Mode full

# Sadece kontrol
.\.ai\scripts\vault-sync.ps1 -Mode check

# Otomatik güncelleme
.\.ai\scripts\vault-auto-update.ps1 -AutoFix

# Bütünlük kontrolü
.\.ai\scripts\vault-integrity-check.ps1 -Detailed
```

## İlgili Dosyalar

- `.ai/index.md` — Ana dizin kataloğu
- `.ai/keys.md` — Navigasyon rehberi
- `.ai/log.md` — Aktivite günlüğü
- `.ai/brain.md` — Mimari kararlar
- `.claude/rules/vault-automation.md` — Otomasyon kuralları
- `.claude/rules/vault-governance.md` — Yönetim kuralları
