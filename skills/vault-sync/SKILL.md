---
name: vault-sync
description: "Vault dosyalarini proje degisimlerine gore otomatik senkronize eder. Trigger: vault guncelle, senkronize et, vault sync, dosyalari guncelle, indeksi guncelle, butunluk kontrol et"
---

# Vault Sync Skill

Proje degisimlerine gore tum vault dosyalarini otomatik gunceller.

## Ne Yapar?

Her onemli proje degisikligi sonrasi:
1. Vault dosyalarini tarar
2. Yeni dosyalari indeksler
3. Referanslari dogrular
4. Butunlugu kontrol eder
5. Gerekirse guncelleme yapar

## Ne Zaman Kullanilir?

- Yeni dosya eklendiginde
- Dosya silindiginde/degistirildiginde
- Yeni API endpoint eklendiginde
- Veritabani semasi degistiginde
- Guvenlik acigi tespit edildiginde
- Deployment yapildiginda

## Guncelleme Alanlari

### Otomatik Guncellenenler

| Dosya | Tetikleme | Guncelleme |
|-------|-----------|------------|
| `.ai/index.md` | Dosya degisikligi | Yeni dosya ekleme |
| `.ai/keys.md` | Yeni anahtar | Anahtar ekleme |
| `.ai/log.md` | Her islem | Log ekleme |
| `.ai/AGENTS.md` | Agent degisikligi | Agent guncelleme |
| `.ai/WORKFLOW.md` | Workflow degisikligi | Workflow guncelleme |

### Manuel Guncelleme Gerektirenler

| Dosya | Guncelleme | Sorumlu |
|-------|-----------|---------|
| `.claude/rules/` | Kural degisikligi | Tech Lead |
| `.workflows/` | Workflow degisikligi | Tech Lead |
| `AGENTS.md` | Agent degisikligi | Tech Lead |
| `WORKFLOW.md` | Workflow degisikligi | Tech Lead |

## Guncelleme Proseduru

### Adim 1: Tarama

```powershell
# Tum vault dosyalarini tara
Get-ChildItem -Path .ai -Recurse -Filter *.md | ForEach-Object {
    $lines = (Get-Content $_.FullName).Count
    Write-Host "$($_.Name): $lines satir"
}
```

### Adim 2: Indeksleme

Yeni dosyalar bulundugunda `.ai/index.md` guncellenir:

```markdown
## [Yeni Dosya Adi]
- **Konum:** `.ai/yeni-dosya.md`
- **Tur:** Agent|Workflow|Decision|Security|Test
- **Satir:** satir_sayisi
- **Son guncelleme:** tarih
```

### Adim 3: Referans Dogrulama

Tum `[[wiki-link]]` referanslari kontrol edilir:
- Kirik link var mi?
- Dairesel referans var mi?
- Eksik referans var mi?

### Adim 4: Butunluk Kontrolu

- Dosya checksum'lari kontrol edilir
- Bozulma tespit edilir
- Gerekirse yedekten geri yuklenir

## Komutlar

### Tam Senkronizasyon

```powershell
.\.ai\scripts\vault-sync.ps1 -Mode full
```

### Sadece Kontrol

```powershell
.\.ai\scripts\vault-sync.ps1 -Mode check
```

### Otomatik Guncelleme

```powershell
.\.ai\scripts\vault-auto-update.ps1 -AutoFix
```

### Butunluk Kontrolu

```powershell
.\.ai\scripts\vault-integrity-check.ps1 -Detailed
```

## Hata Yonetimi

### Yaygin Hatalar

| Hata | Belirti | Cozum |
|------|---------|-------|
| Dosya okunamiyor | Read hatasi | Izinleri kontrol et |
| Referans bulunamadi | Kirik link | Referansi duzelt veya kaldir |
| Butunluk hatasi | Checksum eslesmiyor | Yedekten geri yukle |
| Guncelleme basarisiz | Write hatasi | Izinleri kontrol et |

### Kurtarma Proseduru

1. Sorunu tespit et
2. Kok nedeni bul
3. Cozumu uygula
4. Dogrula
5. Logla

## Monitoring

### Izlenen Metrikler

| Metrik | Hedef | Alert |
|--------|-------|-------|
| Dosya butunlugu | %100 | Bozulma tespit edildiginde |
| Referans gecerliligi | %100 | Kirik link tespit ettiginde |
| Guncelleme sikligi | Haftalik | Guncellenmediginde |
| Hata orani | < %1 | %5 ustunde |

### Alert Politikalari

| Alert | Seviye | Yanit |
|-------|--------|-------|
| Dosya bozuk | Kritik | Hemen duzelt |
| Kirik referans | Yuksek | 1 saat icinde |
| Guncelleme gecikmesi | Orta | 24 saat icinde |
| Boyut asimi | Dusuk | Haftalik inceleme |

## Uyarilar

- Kritik dosyalar (`.ai/brain.md`, `.ai/MEMORY.md`) sadece onayla degistirilir
- Guvenlik dosyalari sadece Security Engineer tarafindan degistirilir
- ADR'ler sadece onay sureciyle degistirilir

## Kontrol Listesi

- [ ] Tum vault dosyalari tarandi
- [ ] Yeni dosyalar indekslendi
- [ ] Silinen dosyalar cikarildi
- [ ] Referanslar kontrol edildi
- [ ] Butunluk dogrulandi
- [ ] Rapor olusturuldu
- [ ] Log guncellendi

## Sorumluluklar

- Her agent bu skill'i kullanabilir
- Guncelleme islemleri vault-updater agent'i tarafindan yapilir
- Guvenlik kontrolleri security-reviewer agent'i tarafindan yapilir
- Raporlama build agent'i tarafindan yapilir

## Zaman Cizelgesi

| Islem | Suresi | Sikligi |
|-------|--------|---------|
| Tam tarama | 5-10 dk | Haftalik |
| Referans kontrolu | 2-5 dk | Her degisiklik |
| Checksum hesaplama | 1-3 dk | Her degisiklik |
| Rapor olusturma | 1 dk | Her calistirma |
| Indeks guncelleme | 1-2 dk | Her degisiklik |

## Basari Kriterleri

- Tum dosyalar guncel
- Kirik referans yok
- Butunluk dogrulandi
- Indeks guncellendi
- Rapor olusturuldu
- Log guncellendi

## Risk Degerlendirmesi

| Risk | Olasilik | Etki | Onlem |
|------|----------|------|-------|
| Dosya kaybi | Dusuk | Yuksek | Yedekleme |
| Veri bozulma | Dusuk | Kritik | Checksum |
| Kirik referans | Orta | Orta | Otomatik kontrol |
| Guncelleme hatasi | Dusuk | Dusuk | Loglama |

## Entegrasyon

### Claude Entegrasyonu
- `.claude/rules/auto-update-vault.md` kurallarini uygular
- `.claude/commands/vault-sync.md` komutunu calistirir
- `.claude/commands/vault-update.md` komutunu calistirir

### OpenCode Entegrasyonu
- `.opencode/agent/vault-updater.md` dosyasini kullanir
- `.opencode/command/vault-sync.md` komutunu calistirir
- `.opencode/command/vault-update.md` komutunu calistirir

### Git Entegrasyonu
- Pre-commit hook'lari
- Post-commit islemleri
- Merge hook'lari

## Dokumantasyon

### Guncelleme Logu

Her guncelleme su formatta loglanir:

```
[YYYY-MM-DD HH:MM:SS] [INFO] [vault-sync] [UPDATE] [DOSYA] [ACIKLAMA]
```

### Rapor Formati

```markdown
# Vault Sync Raporu

**Tarih:** YYYY-MM-DD HH:MM
**Degerlendirme:** Vault Sync Skill

## Yapilan Islemler
- [Islem 1]
- [Islem 2]

## Sonuclar
- [Sonuc 1]
- [Sonuc 2]

## Onceriler
- [Oneri 1]
- [Oneri 2]
```

## Son Notlar

Bu skill, CoreMusic vault'unun guncel ve tutarli olmasini saglamak icin tasarlanmistir. Her onemli proje degisikligi sonrasi calistirilmasi onerilir. Duzenli olarak performansi ve dogrulugu olculmeli, gerekirse ayarlamalar yapilmalidir.

---

## Ek Bolum 1: Skill Kullanim Kilavuzu

### Baslangic

```bash
# Skill'i yukle
vault-sync

# Parametrelerle
vault-sync --mode=full --target=all

# Dry run
vault-sync --dry-run
```

### Parametreler

| Parametre | Aciklama | Varsayilan |
|-----------|----------|------------|
| --mode | Calisma modu | incremental |
| --target | Hedef dosyalar | all |
| --force | Zorla calistir | false |
| --dry-run | Test modu | false |
| --verbose | Detayli cikti | false |

### Modlar

| Mod | Aciklama | Kullanim |
|-----|----------|----------|
| full | Tum vault'u guncelle | Buyuk degisiklikler |
| incremental | Sadece degisenleri | Kucuk degisiklikler |
| validate | Sadece dogrula | Kontrol |
| report | Rapor olustur | Analiz |

---

## Ek Bolum 2: Vault Yapisal Analiz

### Dosya Kategorileri

| Kategori | Dosya Turu | Icerik | Guncelleme Sıklığı |
|----------|------------|--------|---------------------|
| Agent | .md | Agent tanimlari | Her gorev |
| Skill | .md | Skill tanimlari | Gerektiginde |
| Command | .md | Komut tanimlari | Gerektiginde |
| Workflow | .md | Is akisi tanimlari | Haftalik |
| Rule | .md | Kural tanimlari | Aylik |
| Script | .ps1 | Otomasyon | Gerektiginde |
| Config | .json | Yapilandirma | Gerektiginde |

### Dosya Boyut Analizi

```powershell
# vault-size-analysis.ps1
param(
    [string]$VaultPath = ".ai"
)

$files = Get-ChildItem -Path $VaultPath -Recurse -Include "*.md","*.ps1","*.json"

$analysis = $files | ForEach-Object {
    $content = Get-Content $_.FullName
    [PSCustomObject]@{
        Name = $_.Name
        Extension = $_.Extension
        Lines = $content.Count
        Size = $_.Length
        Category = switch ($_.Extension) {
            ".md" { "Dokuman" }
            ".ps1" { "Script" }
            ".json" { "Config" }
            default { "Diger" }
        }
    }
}

# Ozet
$summary = $analysis | Group-Object Category | ForEach-Object {
    [PSCustomObject]@{
        Category = $_.Name
        Count = $_.Count
        TotalLines = ($_.Group | Measure-Object Lines -Sum).Sum
        TotalSize = ($_.Group | Measure-Object Size -Sum).Sum
    }
}

Write-Host "=== VAULT BOYUT ANALIZI ===" -ForegroundColor Cyan
$summary | Format-Table -AutoSize

# Kucuk dosyalar (1000 alti)
$smallFiles = $analysis | Where-Object { $_.Lines -lt 1000 }
if ($smallFiles) {
    Write-Host "`n1000 SATIRDAN AZ DOSYALAR:" -ForegroundColor Yellow
    $smallFiles | Format-Table Name, Lines -AutoSize
}

# Buyuk dosyalar (5000 ustu)
$largeFiles = $analysis | Where-Object { $_.Lines -gt 5000 }
if ($largeFiles) {
    Write-Host "`n5000 SATIRDAN FAZLA DOSYALAR:" -ForegroundColor Yellow
    $largeFiles | Format-Table Name, Lines -AutoSize
}
```

---

## Ek Bolum 3: Guncelleme Stratejileri

### Strateji Secim Matrisi

| Durum | Strateji | Agent | Sure |
|-------|----------|-------|------|
| Yeni API | Full guncelleme | Backend | 2-4 saat |
| UI degisikligi | Incremental | UI | 1-2 saat |
| DB migration | Full guncelleme | Data | 3-5 saat |
| Guvenlik acigi | Immediate | Security | 1 saat |
| Test ekleme | Incremental | QA | 1-2 saat |
| Deployment | Full guncelleme | DevOps | 2-3 saat |

### Guncelleme Akis Diyagrami

```
┌─────────────────────────────────────────────────────────────┐
│                 VAULT GUNCELLEME AKISI                         │
│                                                               │
│  ┌─────────────┐              ┌─────────────┐                │
│  │ Degisiklik   │              │ Analiz      │                │
│  │ Tespiti      │─────────────►│             │                │
│  └─────────────┘              └─────────────┘                │
│         │                           │                         │
│         │                           │                         │
│  ┌─────────────┐              ┌─────────────┐                │
│  │ Agent       │              │ Guncelleme  │                │
│  │ Secimi      │◄─────────────│ Planlama    │                │
│  └─────────────┘              └─────────────┘                │
│         │                           │                         │
│         │                           │                         │
│  ┌─────────────┐              ┌─────────────┐                │
│  │ Uygulama    │              │ Dogrulama   │                │
│  │             │─────────────►│             │                │
│  └─────────────┘              └─────────────┘                │
│         │                           │                         │
│         │                           │                         │
│  ┌─────────────┐              ┌─────────────┐                │
│  │ Raporlama   │              │ Tamamlandi  │                │
│  │             │─────────────►│             │                │
│  └─────────────┘              └─────────────┘                │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### Guncelleme Sablonlari

```markdown
# Guncelleme Sablonu

## Degisiklik Bilgileri
- **Tarih:** YYYY-MM-DD HH:MM
- **Agent:** [Agent adi]
- **Tur:** [Full/Incremental/Validate]

## Degisiklik Detaylari
- **Dosya:** [Dosya yolu]
- **Eski durum:** [Aciklama]
- **Yeni durum:** [Aciklama]

## Etkilenen Dosyalar
- [Dosya 1]
- [Dosya 2]

## Sonraki Adimlar
- [Adim 1]
- [Adim 2]

## Onay
- [ ] Tech Lead onayladi
- [ ] QA dogruladi
```

---

## Ek Bolum 4: Vault Entegrasyonu

### Entegrasyon Noktalari

```
┌─────────────────────────────────────────────────────────────┐
│                 VAULT ENTTEGRASYONU                            │
│                                                               │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐          │
│  │    Git      │  │   CI/CD     │  │  Monitoring  │          │
│  │             │  │             │  │             │          │
│  │ Commit      │  │ Build       │  │ Metrics     │          │
│  │ Push        │  │ Deploy      │  │ Alerts      │          │
│  └─────────────┘  └─────────────┘  └─────────────┘          │
│         │                 │                 │                  │
│         │                 │                 │                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐          │
│  │  Testing    │  │  Security   │  │  Database   │          │
│  │             │  │             │  │             │          │
│  │ Test        │  │ Scan        │  │ Schema      │          │
│  │ Coverage    │  │ Audit       │  │ Migration   │          │
│  └─────────────┘  └─────────────┘  └─────────────┘          │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### Entegrasyon Scripti

```powershell
# vault-integration.ps1
param(
    [string]$Action = "sync",
    [string]$Target = "all"
)

switch ($Action) {
    "sync" {
        Write-Host "=== VAULT SENKRONIZASYONU ===" -ForegroundColor Cyan
        
        # Git durumunu kontrol et
        $gitStatus = git status --porcelain
        if ($gitStatus) {
            Write-Host "Git degisiklikleri var:" -ForegroundColor Yellow
            
            # Degisen dosyalari belirle
            $changedFiles = $gitStatus | ForEach-Object {
                $_.Substring(3)
            }
            
            # Vault dosyalarini filtrele
            $vaultFiles = $changedFiles | Where-Object {
                $_ -match "^\.ai/|\.claude/|\.opencode/"
            }
            
            if ($vaultFiles) {
                Write-Host "Vault dosyalari degismis:" -ForegroundColor Yellow
                $vaultFiles | ForEach-Object { Write-Host "  - $_" }
                
                # Vault guncelleme
                foreach ($file in $vaultFiles) {
                    Write-Host "Guncelleniyor: $file" -ForegroundColor Cyan
                    # Guncelleme mantigi burada
                }
            }
        }
    }
    
    "backup" {
        Write-Host "=== VAULT YEDEGI ===" -ForegroundColor Cyan
        
        $backupDir = "backups/vault-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
        New-Item -ItemType Directory -Path $backupDir -Force
        
        # Vault dosyalarini kopyala
        Copy-Item -Path ".ai/*" -Destination $backupDir -Recurse -ErrorAction SilentlyContinue
        Copy-Item -Path ".claude/*" -Destination $backupDir -Recurse -ErrorAction SilentlyContinue
        Copy-Item -Path ".opencode/*" -Destination $backupDir -Recurse -ErrorAction SilentlyContinue
        
        Write-Host "Yedek alindi: $backupDir" -ForegroundColor Green
    }
    
    "restore" {
        Write-Host "=== VAULT GERI YUKLEME ===" -ForegroundColor Cyan
        
        $latestBackup = Get-ChildItem -Path "backups" -Directory |
            Where-Object { $_.Name -like "vault-*" } |
            Sort-Object Name -Descending |
            Select-Object -First 1
        
        if ($latestBackup) {
            Write-Host "Son yedek: $($latestBackup.Name)" -ForegroundColor Yellow
            
            # Geri yukleme onayi
            $confirm = Read-Host "Devam etmek istiyor musunuz? (E/H)"
            if ($confirm -eq "E") {
                Copy-Item -Path "$($latestBackup.FullName)/*" -Destination ".ai/" -Recurse -Force
                Write-Host "Vault geri yuklendi" -ForegroundColor Green
            }
        } else {
            Write-Host "Yedek bulunamadi" -ForegroundColor Red
        }
    }
}
```

---

## Ek Bolum 5: Vault Optimizasyonu

### Optimizasyon Stratejileri

| Strateji | Aciklama | Etki | Zorluk |
|----------|----------|------|--------|
| Dosya birlestirme | Kucuk dosyalari birlestir | Orta | Dusuk |
| Icerik temizleme | Tekrar eden icerikleri temizle | Yuksek | Orta |
| Referans guncelleme | Eski referanslari guncelle | Orta | Dusuk |
| Format standartlastirma | Tutarli format olustur | Dusuk | Dusuk |
| Index olusturma | Arama icin index olustur | Yuksek | Yuksek |

### Optimizasyon Scripti

```powershell
# vault-optimize.ps1
param(
    [string]$VaultPath = ".ai",
    [switch]$DryRun
)

Write-Host "=== VAULT OPTIMIZASYONU ===" -ForegroundColor Cyan

# 1. Bos dosyalari bul
Write-Host "`n1. Bos dosyalar taraniyor..." -ForegroundColor Yellow
$emptyFiles = Get-ChildItem -Path $VaultPath -Recurse -Filter "*.md" |
    Where-Object { (Get-Content $_.FullName -Raw).Length -eq 0 }

if ($emptyFiles) {
    Write-Host "Bos dosyalar bulundu:" -ForegroundColor Red
    $emptyFiles | ForEach-Object { 
        Write-Host "  - $($_.Name)" -ForegroundColor Red
        if (-not $DryRun) {
            Remove-Item $_.FullName -Force
            Write-Host "    Silindi" -ForegroundColor Green
        }
    }
} else {
    Write-Host "  Bos dosya bulunamadi" -ForegroundColor Green
}

# 2. Kucuk dosyalari bul
Write-Host "`n2. Kucuk dosyalar taraniyor..." -ForegroundColor Yellow
$smallFiles = Get-ChildItem -Path $VaultPath -Recurse -Filter "*.md" |
    Where-Object { (Get-Content $_.FullName).Count -lt 100 }

if ($smallFiles) {
    Write-Host "Kucuk dosyalar bulundu:" -ForegroundColor Yellow
    $smallFiles | ForEach-Object { 
        $lines = (Get-Content $_.FullName).Count
        Write-Host "  - $($_.Name): $lines satir" -ForegroundColor Yellow
    }
} else {
    Write-Host "  Kucuk dosya bulunamadi" -ForegroundColor Green
}

# 3. Tekrar eden icerikleri bul
Write-Host "`n3. Tekrar eden icerikler taraniyor..." -ForegroundColor Yellow
$files = Get-ChildItem -Path $VaultPath -Recurse -Filter "*.md"
$contentHash = @{}
$duplicates = @()

foreach ($file in $files) {
    $content = Get-Content -Path $file.FullName -Raw
    $hash = [System.BitConverter]::ToString(
        [System.Security.Cryptography.SHA256]::Create().ComputeHash(
            [System.Text.Encoding]::UTF8.GetBytes($content)
        )
    )
    
    if ($contentHash.ContainsKey($hash)) {
        $duplicates += [PSCustomObject]@{
            File1 = $file.Name
            File2 = $contentHash[$hash]
        }
    } else {
        $contentHash[$hash] = $file.Name
    }
}

if ($duplicates) {
    Write-Host "Tekrar eden icerikler bulundu:" -ForegroundColor Red
    $duplicates | Format-Table -AutoSize
} else {
    Write-Host "  Tekrar eden icerik bulunamadi" -ForegroundColor Green
}

# 4. Rapor olustur
Write-Host "`n4. Rapor olusturuluyor..." -ForegroundColor Yellow
$report = @{
    Date = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    TotalFiles = $files.Count
    EmptyFiles = $emptyFiles.Count
    SmallFiles = $smallFiles.Count
    Duplicates = $duplicates.Count
}

$reportPath = "$VaultPath/optimization-report.json"
$report | ConvertTo-Json | Set-Content -Path $reportPath
Write-Host "Rapor kaydedildi: $reportPath" -ForegroundColor Green

Write-Host "`n=== OPTIMIZASYON TAMAMLANDI ===" -ForegroundColor Cyan
```

---

## Ek Bolum 6: Vault Monitoring

### Monitoring Dashboard

```
┌─────────────────────────────────────────────────────────────┐
│                 VAULT MONITORING DASHBOARD                     │
│                                                               │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ DURUM: OK                                          │    │
│  │ Son guncelleme: YYYY-MM-DD HH:MM                   │    │
│  │ Toplam dosya: X                                     │    │
│  │ Toplam boyut: X MB                                  │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                               │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐          │
│  │ DOSYA DURUMU│  │ PERFORMANS  │  │ GUVENLIK    │          │
│  │             │  │             │  │             │          │
│  │ Agent: X    │  │ Guncelleme: │  │ Son tarama: │          │
│  │ Skill: X    │  │   X dk      │  │   Temiz     │          │
│  │ Command: X  │  │ Dogrulama:  │  │             │          │
│  │ Workflow: X │  │   X dk      │  │             │          │
│  └─────────────┘  └─────────────┘  └─────────────┘          │
│                                                               │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ TREND                                               │    │
│  │                                                     │    │
│  │ Dosya sayisi:  ████████████████████  Stabil        │    │
│  │ Guncelleme:    ████████████████████  Normal        │    │
│  │ Hata orani:    ██                    Dusuk          │    │
│  │                                                     │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### Monitoring Metrikleri

| Metrik | Hedef | Uyari | Kritik |
|--------|-------|-------|--------|
| Dosya sayisi | Stabil | %10 degisim | %20 degisim |
| Toplam boyut | Stabil | %10 degisim | %20 degisim |
| Guncelleme suresi | <5dk | >10dk | >30dk |
| Hata orani | 0 | >0 | >5% |
| Dogrulama basari | %100 | <95% | <90% |

### Monitoring Scripti

```powershell
# vault-monitor.ps1
param(
    [string]$VaultPath = ".ai",
    [string]$MetricsPath = ".ai/metrics",
    [int]$AlertThreshold = 10
)

# Metrik dizinini olustur
if (-not (Test-Path $MetricsPath)) {
    New-Item -ItemType Directory -Path $MetricsPath -Force
}

# Mevcut metrikleri topla
$currentMetrics = @{
    Timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    FileCount = (Get-ChildItem -Path $VaultPath -Recurse -Filter "*.md").Count
    TotalSize = (Get-ChildItem -Path $VaultPath -Recurse -Filter "*.md" | 
        Measure-Object -Property Length -Sum).Sum
    LastModified = (Get-ChildItem -Path $VaultPath -Recurse -Filter "*.md" |
        Sort-Object LastWriteTime -Descending | Select-Object -First 1).LastWriteTime
}

# Onceki metrikleri yukle
$previousMetricsFile = Join-Path $MetricsPath "latest.json"
if (Test-Path $previousMetricsFile) {
    $previousMetrics = Get-Content $previousMetricsFile | ConvertFrom-Json
    
    # Degisim hesapla
    $fileChange = $currentMetrics.FileCount - $previousMetrics.FileCount
    $sizeChange = $currentMetrics.TotalSize - $previousMetrics.TotalSize
    
    # Uyarilar
    $alerts = @()
    
    if ([Math]::Abs($fileChange) -gt ($previousMetrics.FileCount * ($AlertThreshold / 100))) {
        $alerts += "Dosya sayisinda buyuk degisim: $fileChange"
    }
    
    if ([Math]::Abs($sizeChange) -gt ($previousMetrics.TotalSize * ($AlertThreshold / 100))) {
        $alerts += "Boyutta buyuk degisim: $([Math]::Round($sizeChange / 1MB, 2)) MB"
    }
    
    if ($alerts) {
        Write-Warning "VAULT UYARILARI:"
        $alerts | ForEach-Object { Write-Warning "  - $_" }
    }
}

# Metrikleri kaydet
$currentMetrics | ConvertTo-Json | Set-Content -Path $previousMetricsFile

# Rapor olustur
$report = @"
# Vault Monitoring Raporu

## Tarih: $($currentMetrics.Timestamp)

## Durum
- Dosya Sayisi: $($currentMetrics.FileCount)
- Toplam Boyut: $([Math]::Round($currentMetrics.TotalSize / 1MB, 2)) MB
- Son Guncelleme: $($currentMetrics.LastModified)

## Tavsiyeler
- Duzenli yedek alin
- Eski dosyalari temizleyin
- Buyuk dosyalari bolun
- Icerikleri birlestirin
"@

$reportFile = Join-Path $MetricsPath "report-$(Get-Date -Format 'yyyyMMdd-HHmmss').md"
$report | Set-Content -Path $reportFile

Write-Host "Monitoring tamamlandi" -ForegroundColor Green
Write-Host "Rapor: $reportFile" -ForegroundColor Cyan
```

---

## Ek Bolum 7: Vault Guvenlik

### Guvenlik Kontrolleri

| Kontrol | Aciklama | Onem |
|---------|----------|------|
| Secret taramasi | Dosyalarda secret yok | Kritik |
| Dosya izinleri | Guvenli izinler | Yuksek |
| Erisim loglari | Kim ne zaman girdi | Orta |
| Yedekleme | Duzenli yedek | Yuksek |
| Sifreleme | Hassas veriler sifreli | Kritik |

### Guvenlik Scripti

```powershell
# vault-security.ps1
param(
    [string]$VaultPath = ".ai",
    [string]$ReportPath = ".ai/security-reports"
)

Write-Host "=== VAULT GUVENLIK TARAMASI ===" -ForegroundColor Cyan

# Rapor dizinini olustur
if (-not (Test-Path $ReportPath)) {
    New-Item -ItemType Directory -Path $ReportPath -Force
}

$securityIssues = @()

# 1. Secret taramasi
Write-Host "`n1. Secret taramasi yapiliyor..." -ForegroundColor Yellow
$secretPatterns = @(
    'password\s*=\s*["\x27].*["\x27]',
    'secret\s*=\s*["\x27].*["\x27]',
    'api_key\s*=\s*["\x27].*["\x27]',
    'token\s*=\s*["\x27].*["\x27]',
    'private_key\s*=\s*["\x27]',
    'BEGIN\s+(RSA\s+)?PRIVATE\s+KEY'
)

$files = Get-ChildItem -Path $VaultPath -Recurse -Include "*.md","*.ps1","*.json"

foreach ($file in $files) {
    $content = Get-Content -Path $file.FullName -Raw -ErrorAction SilentlyContinue
    if ($content) {
        foreach ($pattern in $secretPatterns) {
            if ($content -match $pattern) {
                $securityIssues += [PSCustomObject]@{
                    File = $file.Name
                    Issue = "Potansiel secret bulundu"
                    Pattern = $pattern
                    Severity = "Critical"
                }
                Write-Host "  CRITICAL: $($file.Name) - Secret bulundu" -ForegroundColor Red
            }
        }
    }
}

# 2. Dosya izinleri
Write-Host "`n2. Dosya izinleri kontrol ediliyor..." -ForegroundColor Yellow
$files | ForEach-Object {
    try {
        $acl = Get-Acl $_.FullName -ErrorAction SilentlyContinue
        if ($acl) {
            $access = $acl.Access | Where-Object { 
                $_.FileSystemRights -match "FullControl" -and 
                $_.IdentityReference -notmatch "SYSTEM|Administrators|Users" 
            }
            
            if ($access) {
                $securityIssues += [PSCustomObject]@{
                    File = $_.Name
                    Issue = "Genis erisim izni"
                    Pattern = $access.IdentityReference
                    Severity = "High"
                }
                Write-Host "  HIGH: $($_.Name) - Genis erisim" -ForegroundColor Yellow
            }
        }
    } catch {
        # ACL okunamadi
    }
}

# 3. Buyuk dosyalar
Write-Host "`n3. Buyuk dosyalar kontrol ediliyor..." -ForegroundColor Yellow
$largeFiles = $files | Where-Object { $_.Length -gt 1MB }
if ($largeFiles) {
    $largeFiles | ForEach-Object {
        $securityIssues += [PSCustomObject]@{
            File = $_.Name
            Issue = "Buyuk dosya"
            Pattern = "$([Math]::Round($_.Length / 1MB, 2)) MB"
            Severity = "Medium"
        }
        Write-Host "  MEDIUM: $($_.Name) - $([Math]::Round($_.Length / 1MB, 2)) MB" -ForegroundColor Yellow
    }
}

# 4. Eski dosyalar
Write-Host "`n4. Eski dosyalar kontrol ediliyor..." -ForegroundColor Yellow
$oldFiles = $files | Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-90) }
if ($oldFiles) {
    $oldFiles | ForEach-Object {
        $securityIssues += [PSCustomObject]@{
            File = $_.Name
            Issue = "Eski dosya"
            Pattern = $_.LastWriteTime.ToString("yyyy-MM-dd")
            Severity = "Low"
        }
        Write-Host "  LOW: $($_.Name) - Son guncelleme: $($_.LastWriteTime)" -ForegroundColor Yellow
    }
}

# Rapor olustur
$report = @"
# Vault Guvenlik Raporu

## Tarih: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")

## Ozet
- Toplam dosya: $($files.Count)
- Guvenlik sorunu: $($securityIssues.Count)
- Kritik: $(($securityIssues | Where-Object { $_.Severity -eq "Critical" }).Count)
- Yuksek: $(($securityIssues | Where-Object { $_.Severity -eq "High" }).Count)
- Orta: $(($securityIssues | Where-Object { $_.Severity -eq "Medium" }).Count)
- Dusuk: $(($securityIssues | Where-Object { $_.Severity -eq "Low" }).Count)

## Detaylar
$($securityIssues | Format-Table -AutoSize | Out-String)

## Oneriler
1. Secret'lar hemen silinmeli
2. Dosya izinleri daraltilmeli
3. Eski dosyalar arsivlenmeli
4. Buyuk dosyalar bolunmeli
"@

$reportFile = Join-Path $ReportPath "security-$(Get-Date -Format 'yyyyMMdd-HHmmss').md"
$report | Set-Content -Path $reportFile

Write-Host "`n=== TARAMA TAMAMLANDI ===" -ForegroundColor Cyan
Write-Host "Rapor: $reportFile" -ForegroundColor Cyan

if ($securityIssues | Where-Object { $_.Severity -eq "Critical" }) {
    Write-Host "KRITIK SORUNLAR VAR!" -ForegroundColor Red
    exit 1
}
```

---

## Ek Bolum 8: Vault Raporlama

### Rapor Turleri

| Rapor | Tur | Siklik | Hedef |
|-------|-----|--------|-------|
| Durum | Gunluk | Gunluk | Yonetici |
| Performans | Haftalik | Haftalik | Teknik ekip |
| Guvenlik | Haftalik | Haftalik | Guvenlik ekibi |
| Kalite | Aylik | Aylik | Tum ekip |
| Stratejik | Ceyreklik | Ceyreklik | Yonetim |

### Rapor Sablonu

```markdown
# Vault Durum Raporu

## Tarih
YYYY-MM-DD

## Genel Durum
- **Dosya sayisi:** X
- **Toplam boyut:** X MB
- **Son guncelleme:** YYYY-MM-DD HH:MM
- **Durum:** [Iyi/Uyari/Kritik]

## Dosya Analizi
| Kategori | Dosya Sayisi | Boyut | Son Guncelleme |
|----------|--------------|-------|----------------|
| Agent | X | X MB | YYYY-MM-DD |
| Skill | X | X MB | YYYY-MM-DD |
| Command | X | X MB | YYYY-MM-DD |
| Workflow | X | X MB | YYYY-MM-DD |
| Rule | X | X MB | YYYY-MM-DD |

## Guncelleme Gecmisi
| Tarih | Dosya | Tur | Sorumlu |
|-------|-------|-----|---------|
| YYYY-MM-DD | AGENTS.md | Guncelleme | Backend Architect |

## Sorunlar
| Sorun | Onem | Durum | Cozum |
|-------|------|-------|-------|
| Eski versiyon | Orta | Devam | Guncelle |

## Oneriler
1. [Oneri 1]
2. [Oneri 2]

## Sonraki Adimlar
1. [Adim 1]
2. [Adim 2]
```

---

## Ek Bolum 9: Vault Otomasyonu

### Otomasyon Senaryolari

| Senaryo | Tetikleyici | Aksiyon | Agent |
|---------|-------------|---------|-------|
| Yeni dosya | Git commit | Vault'a ekle | Vault Updater |
| Degisiklik | Git push | Vault'u guncelle | Vault Updater |
| Hata | CI/CD basarisiz | Log ekle | DevOps |
| Guvenlik | Tarama sonucu | Rapor olustur | Security |
| Performans | Metrik esik | Uyari gonder | DevOps |

### Otomasyon Scripti

```powershell
# vault-automation.ps1
param(
    [string]$Event = "commit",
    [string]$CommitMessage = ""
)

Write-Host "=== VAULT OTOMASYONU ===" -ForegroundColor Cyan
Write-Host "Olay: $Event" -ForegroundColor Yellow

switch ($Event) {
    "commit" {
        Write-Host "Commit isleniyor..." -ForegroundColor Yellow
        
        # Yeni dosyalari bul
        $newFiles = git diff --cached --name-only --diff-filter=A 2>$null
        
        if ($newFiles) {
            $vaultFiles = $newFiles | Where-Object { 
                $_ -match "\.md$|\.ps1$|\.json$" 
            }
            
            if ($vaultFiles) {
                Write-Host "Yeni vault dosyalari:" -ForegroundColor Green
                $vaultFiles | ForEach-Object { Write-Host "  - $_" }
                
                # Vault index'ini guncelle
                # (Burada vault index guncelleme mantigi olacak)
            }
        }
    }
    
    "push" {
        Write-Host "Push isleniyor..." -ForegroundColor Yellow
        
        # Vault dosyalarini kontrol et
        $vaultFiles = Get-ChildItem -Path ".ai",".claude",".opencode" -Recurse -Include "*.md","*.ps1","*.json" -ErrorAction SilentlyContinue
        
        if ($vaultFiles) {
            Write-Host "Vault dosyalari: $($vaultFiles.Count)" -ForegroundColor Green
            
            # Degisiklikleri kontrol et
            $changedFiles = git diff --name-only 2>$null
            if ($changedFiles) {
                $vaultChanges = $changedFiles | Where-Object { 
                    $_ -match "^\.ai/|^\.claude/|^\.opencode/" 
                }
                
                if ($vaultChanges) {
                    Write-Host "Degisen vault dosyalari:" -ForegroundColor Yellow
                    $vaultChanges | ForEach-Object { Write-Host "  - $_" }
                }
            }
        }
    }
    
    "error" {
        Write-Host "Hata loglaniyor..." -ForegroundColor Red
        
        $errorLog = @"
## Hata Logu

**Tarih:** $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
**Olay:** $Event
**Mesaj:** $CommitMessage
**Aksiyon:** Duzeltme gerekli

"@
        
        Add-Content -Path ".ai/log.md" -Value $errorLog
        Write-Host "Hata logu eklendi" -ForegroundColor Green
    }
    
    "security" {
        Write-Host "Guvenlik taramasi baslatiliyor..." -ForegroundColor Yellow
        
        # Guvenlik taramasi
        & ".ai/scripts/vault-security.ps1" -VaultPath ".ai"
    }
}

Write-Host "=== OTOMASYON TAMAMLANDI ===" -ForegroundColor Cyan
```

---

## Ek Bolum 10: Vault Egitimi

### Egitim Programi

| Modul | Icerik | Sure | Hedef |
|-------|--------|------|-------|
| 1 | Vault'a Giris | 1 saat | Tum kullanicilar |
| 2 | Dosya Yapisi | 2 saat | Tum kullanicilar |
| 3 | Guncelleme Sureci | 2 saat | Gelistiriciler |
| 4 | Guvenlik | 1 saat | Tum kullanicilar |
| 5 | Admin | 4 saat | Vault yoneticileri |

### Egitim Materyalleri

```markdown
# Vault Egitim Materyali

## Modul 1: Vault'a Giris

### Neden Vault?
- Bilgi daginikligini onler
- Tutarlilik saglar
- Kolay guncelleme
- Guvenli depolama

### Vault Nedir?
CoreMusic vault, tum proje bilgilerinin merkezi depolamasiidr. Agent tanimlari, skill tanimlari, komut tanimlari ve daha fazlasi burada bulunur.

### Vault Nasil Kullanilir?
1. Dosyalari okuyun
2. Guncellemeleri anlayin
3. Dogru dosyalari guncelleyin
4. Dogrulama yapin

## Modul 2: Dosya Yapisi

### Klasor Yapisi
```
.ai/
├── agents/      # Agent tanimlari
├── skills/      # Skill tanimlari
├── commands/    # Komut tanimlari
├── workflows/   # Is akisi tanimlari
├── rules/       # Kural tanimlari
├── scripts/     # Otomasyon scriptleri
└── logs/        # Log dosyalari
```

### Dosya Turleri
- `.md`: Markdown dosyalari
- `.ps1`: PowerShell scriptleri
- `.json`: JSON dosyalari

## Modul 3: Guncelleme Sureci

### Guncelleme Adimlari
1. Degisikligi belirleyin
2. Etkilenen dosyalari bulun
3. Guncellemeyi planlayin
4. Guncellemeyi uygulayin
5. Dogrulama yapin
6. Rapor olusturun

### Guncelleme Kurallari
- Her guncelleme icin log tutun
- Buyuk degisiklikler icin onay alin
- Geri alma plani hazirlayin
- Test ortaminda test edin

## Modul 4: Guvenlik

### Guvenlik Kurallari
- Dosyalarda secret bulundurmayin
- Guvenli dosya izinleri kullanin
- Duzenli yedek alin
- Erisim loglari tutun

### Guvenli Kod Yazma
- Input validation
- Output encoding
- Parameterized queries
- Secure configuration
```

---

*Vault Sync Skill v1.0.0 - 2026-07-12*
*CoreMusic Orchestration System*
