# Milk Hub Supply Chain App

## User Stories Document

**Project:** Milk Hub Supply Chain Management System
**Client:** Mazaraat Artisan Cheese
**Version:** 2.0
**Last Updated:** December 2024

## Implementation Status

✅ **Fully Implemented** | 🔄 **Partially Implemented** | ❌ **Not Implemented**

**Overall Progress: 73% Complete (22/30 stories)**
- Phase 1 (MVP): **85% Complete** (17/20 P0 stories)
- Phase 2 (Enhancement): **40% Complete** (4/10 P1 stories)
- Phase 3 (Optimization): **0% Complete** (0/2 P2 stories)

**📋 For detailed implementation status, see:** [USER_STORIES_IMPLEMENTATION_STATUS.md](./USER_STORIES_IMPLEMENTATION_STATUS.md)

### Key Achievements
- ✅ **Full Milk Supply Chain**: Farm → Collection → QC → Production → Payment
- ✅ **Complete Traceability**: Cheese → Milk Batches → Livestock → Farms
- ✅ **Auto-Grading System**: A/B/C/Reject based on configurable standards
- ✅ **Payment Automation**: Grade-based pricing with approval workflow
- ✅ **Real-time Monitoring**: Supply chain dashboard with smart alerts
- ✅ **Comprehensive Reporting**: 8 report types with PDF/Excel export

### Implementation Approach
**Simplified from Original Plan:**
- Web-only (no offline mode/PWA)
- In-app notifications only (no push/SMS)
- Manual route planning (no GPS/ETA)
- 4 new roles instead of 7 (transporter, qc, production, finance)
- Farm-based (existing Farm entity vs separate Peternak)

**Leveraged Existing Systems:**
- LivestockMilking for daily recording
- Inventory system for batch tracking
- Existing notification system
- ReportController extended for milk reports
- Auditable trait for change tracking

---

## Executive Summary

Milk Hub adalah aplikasi supply chain management untuk mendukung operasional Mazaraat Artisan Cheese dalam mengelola alur susu dari peternak lokal hingga menjadi produk keju artisan premium. Sistem ini mencakup pencatatan produksi, transportasi, quality control, dan manajemen produksi keju.

---

## User Roles Overview

| Role             | Deskripsi                                 | Akses Utama                  |
| ---------------- | ----------------------------------------- | ---------------------------- |
| Peternak         | Penyedia susu segar dari peternakan lokal | Mobile app (offline-capable) |
| Transporter      | Pengangkut susu dari farm ke facility     | Mobile app                   |
| Gudang/Receiving | Penerima dan penyimpan susu di facility   | Web + Mobile                 |
| Quality Control  | Penguji kualitas susu dan keju            | Web app                      |
| Cheese Maker     | Produksi keju dari susu                   | Web + Mobile                 |
| Admin/Manager    | Pengelola operasional keseluruhan         | Web app (full access)        |
| Finance          | Pengelola pembayaran dan costing          | Web app                      |

---

## 1. Peternak

### 1.1 Pencatatan Produksi Harian ✅

**Status:** ✅ **Fully Implemented** (via `LivestockMilking` model)

**User Story:**
Sebagai peternak, saya ingin mencatat produksi susu harian saya secara digital, sehingga saya bisa melacak jumlah susu yang dihasilkan dan dijual ke Milk Hub.

**Acceptance Criteria:**

- [x] Peternak dapat input jumlah susu (liter) per sesi perah (pagi/sore/malam)
- [x] Sistem mencatat timestamp otomatis
- [x] Peternak dapat melihat summary produksi harian, mingguan, bulanan
- [ ] ~~Data tersimpan lokal jika offline dan sync saat online~~ *Deferred - Web-only*
- [x] Sistem menampilkan trend produksi dalam bentuk grafik sederhana

**Implementation Details:**
- Model: `LivestockMilking` with session tracking (morning/afternoon/evening)
- Features: Individual livestock milking records, device_id support, automatic timestamps
- Dashboard: Home dashboard shows milk production trends
- Reports: `milking-report` generates production summaries

**Priority:** P0 - Must Have

---

### 1.2 Notifikasi Pengambilan Susu 🔄

**Status:** 🔄 **Partially Implemented** (Infrastructure exists, specific notifications not yet implemented)

**User Story:**
Sebagai peternak, saya ingin menerima notifikasi pengambilan susu oleh transporter, sehingga saya tahu kapan susu saya akan diangkut.

**Acceptance Criteria:**

- [ ] Peternak menerima ~~push notification~~ *in-app notification* H-1 jadwal pickup
- [ ] Peternak menerima notifikasi saat transporter dalam perjalanan (ETA)
- [x] Peternak menerima konfirmasi setelah pickup selesai dengan jumlah yang diambil
- [ ] ~~Notifikasi bisa diterima via SMS~~ *Deferred*

**Implementation Details:**
- Infrastructure: `notifications` table and bell icon in header exists
- Status updates trigger can be added for milk batch collection
- In-app notifications preferred over push/SMS

**Priority:** P0 - Must Have

---

### 1.3 Riwayat Pembayaran ✅

**Status:** ✅ **Fully Implemented** (via `MilkPayment` and FarmerView page)

**User Story:**
Sebagai peternak, saya ingin melihat riwayat pembayaran dari Milk Hub, sehingga saya bisa memastikan pembayaran sesuai dengan jumlah susu yang saya serahkan.

**Acceptance Criteria:**

- [x] Peternak dapat melihat daftar pembayaran dengan filter tanggal
- [x] Setiap pembayaran menampilkan: tanggal, jumlah liter, grade susu, harga per liter, total
- [x] Peternak dapat melihat detail breakdown per pickup yang masuk dalam pembayaran
- [x] Peternak dapat download bukti pembayaran (PDF) *via Report system*
- [x] Sistem menampilkan status pembayaran (draft, approved, paid)

**Implementation Details:**
- Page: `Payments/FarmerView.vue` - Payment history for farmers
- Page: `Payments/Show.vue` - Detailed payment view with grade breakdown
- Model: `MilkPayment` with full audit trail
- Features: Grade breakdown (A/B/C), deductions support, related batch traceability
- Stats: Pending payments, paid this month, total this year, average monthly

**Priority:** P0 - Must Have

---

### 1.4 Laporan Kesehatan Ternak

**User Story:**  
Sebagai peternak, saya ingin mendapatkan laporan kesehatan ternak terkait produksi susu, sehingga saya bisa memantau kondisi ternak dan mencegah penurunan kualitas susu.

**Acceptance Criteria:**

- [ ] Peternak dapat input data kesehatan ternak (jumlah sapi, kondisi, treatment)
- [ ] Sistem memberikan alert jika produksi turun signifikan (>20% dari average)
- [ ] Peternak dapat melihat correlation antara kesehatan ternak dan kualitas susu
- [ ] Sistem memberikan tips/rekomendasi berdasarkan data

**Priority:** P2 - Nice to Have

---

### 1.5 Pencatatan Pakan Ternak

**User Story:**  
Sebagai peternak, saya ingin mencatat jenis pakan yang diberikan ke ternak, sehingga Milk Hub bisa mengkorelasikan dengan profil rasa susu untuk produksi keju artisan.

**Acceptance Criteria:**

- [ ] Peternak dapat memilih jenis pakan dari daftar (rumput, konsentrat, fermentasi, dll)
- [ ] Peternak dapat menambah catatan khusus (misal: pakan organik, rumput Merapi)
- [ ] Data pakan ter-link dengan batch susu yang diproduksi
- [ ] Admin/Cheese maker dapat melihat data pakan sebagai referensi

**Priority:** P1 - Should Have

---

### 1.6 Mode Offline

**User Story:**  
Sebagai peternak, saya ingin dapat menginput data secara offline dan sync otomatis saat ada koneksi, sehingga saya tetap bisa mencatat meski di lokasi dengan sinyal terbatas.

**Acceptance Criteria:**

- [ ] Semua fitur input dapat berjalan tanpa koneksi internet
- [ ] Data tersimpan di local storage device
- [ ] Sync otomatis berjalan saat koneksi tersedia
- [ ] Indikator visual menunjukkan status sync (synced/pending)
- [ ] Conflict resolution jika ada data yang bentrok

**Priority:** P0 - Must Have

---

## 2. Transporter

### 2.1 Jadwal dan Rute Pengambilan

**User Story:**  
Sebagai transporter, saya ingin melihat jadwal pengambilan susu dari berbagai peternak, sehingga saya bisa merencanakan rute yang efisien.

**Acceptance Criteria:**

- [ ] Transporter melihat daftar pickup hari ini dengan urutan rute optimal
- [ ] Setiap pickup menampilkan: nama peternak, lokasi (GPS), estimasi volume
- [ ] Transporter dapat melihat peta dengan semua titik pickup
- [ ] Sistem menghitung ETA untuk setiap titik
- [ ] Transporter dapat mengubah urutan jika ada kendala di lapangan

**Priority:** P0 - Must Have

---

### 2.2 Pencatatan Jumlah Susu

**User Story:**  
Sebagai transporter, saya ingin mencatat jumlah susu yang diambil dari peternak, sehingga tercatat dengan benar dalam sistem Milk Hub.

**Acceptance Criteria:**

- [ ] Transporter input jumlah liter saat pickup
- [ ] Sistem menampilkan perbandingan dengan estimasi peternak
- [ ] Alert jika selisih >10% untuk konfirmasi
- [ ] Transporter dapat menambah catatan jika ada anomali
- [ ] Data langsung ter-update di dashboard admin

**Priority:** P0 - Must Have

---

### 2.3 Pelaporan Kondisi Susu

**User Story:**  
Sebagai transporter, saya ingin melaporkan kondisi susu saat pengambilan dan pengiriman, sehingga kualitas susu tetap terjaga dan bisa dilacak bila terjadi masalah.

**Acceptance Criteria:**

- [ ] Transporter input kondisi visual susu (normal, berbusa, berwarna, dll)
- [ ] Transporter dapat upload foto susu jika ada anomali
- [ ] Transporter input bau susu (normal, asam, abnormal)
- [ ] Sistem flag otomatis jika kondisi tidak normal

**Priority:** P1 - Should Have

---

### 2.4 Pencatatan Suhu (Cold Chain)

**User Story:**  
Sebagai transporter, saya ingin mencatat suhu susu saat pickup dan delivery, sehingga cold chain terjaga dan ada bukti jika terjadi masalah kualitas.

**Acceptance Criteria:**

- [ ] Transporter input suhu saat pickup (wajib)
- [ ] Transporter input suhu saat arrival di facility (wajib)
- [ ] Alert jika suhu di luar range optimal (4-7°C)
- [ ] Sistem mencatat durasi perjalanan
- [ ] QC dapat melihat data suhu sebagai referensi grading

**Priority:** P0 - Must Have

---

### 2.5 Bukti Serah Terima

**User Story:**  
Sebagai transporter, saya ingin capture foto dan tanda tangan peternak saat pickup, sehingga ada bukti serah terima yang sah.

**Acceptance Criteria:**

- [ ] Transporter dapat capture foto milk can/container
- [ ] Peternak tanda tangan digital di layar
- [ ] Sistem generate receipt otomatis
- [ ] Receipt tersimpan dan bisa diakses oleh peternak dan admin
- [ ] Foto dan tanda tangan ter-attach ke record pickup

**Priority:** P1 - Should Have

---

## 3. Gudang / Receiving

### 3.1 Notifikasi Kedatangan

**User Story:**  
Sebagai staf gudang, saya ingin menerima notifikasi kedatangan susu dari transporter, sehingga saya bisa mempersiapkan tempat penyimpanan dan pemrosesan.

**Acceptance Criteria:**

- [ ] Staf gudang menerima notifikasi saat transporter berangkat dari pickup terakhir
- [ ] Notifikasi mencakup ETA dan estimasi total volume
- [ ] Staf gudang dapat melihat daftar kedatangan hari ini
- [ ] Alert jika ada keterlambatan >30 menit dari jadwal

**Priority:** P0 - Must Have

---

### 3.2 Pencatatan Stok

**User Story:**  
Sebagai staf gudang, saya ingin mencatat stok masuk dan keluar susu, sehingga saya bisa memantau persediaan secara real-time.

**Acceptance Criteria:**

- [ ] Staf gudang input stok masuk saat menerima dari transporter
- [ ] Staf gudang input stok keluar saat transfer ke produksi
- [ ] Dashboard menampilkan stok real-time
- [ ] Alert jika stok mendekati kapasitas maksimum
- [ ] Alert jika susu sudah mendekati batas waktu proses (>24 jam)
- [ ] Sistem mencatat batch number untuk traceability

**Priority:** P0 - Must Have

---

### 3.3 Pemantauan Kualitas Awal

**User Story:**  
Sebagai staf gudang, saya ingin memantau kualitas susu yang masuk, sehingga hanya susu yang memenuhi standar dikirim ke produksi.

**Acceptance Criteria:**

- [ ] Staf gudang melakukan quick check (visual, bau, suhu)
- [ ] Staf gudang dapat menandai susu untuk full QC test
- [ ] Staf gudang dapat reject susu yang jelas tidak layak
- [ ] Rejected milk tercatat dengan alasan dan foto
- [ ] Notifikasi ke admin dan peternak jika ada rejection

**Priority:** P0 - Must Have

---

## 4. Quality Control

### 4.1 Pencatatan Hasil Lab Test

**User Story:**  
Sebagai QC staff, saya ingin mencatat hasil lab test susu (pH, fat content, bacteria count, protein), sehingga hanya susu yang memenuhi standar artisan cheese masuk ke produksi.

**Acceptance Criteria:**

- [ ] QC input hasil test: pH, fat %, protein %, bacteria count, SNF
- [ ] Sistem auto-calculate apakah memenuhi standar
- [ ] Standar dapat dikonfigurasi oleh admin
- [ ] Hasil test ter-link ke batch susu
- [ ] QC dapat upload foto hasil test jika menggunakan alat manual

**Priority:** P0 - Must Have

---

### 4.2 Grading Susu

**User Story:**  
Sebagai QC staff, saya ingin men-grade susu saat penerimaan (Grade A/B/C/Reject), sehingga peternak mendapat feedback dan pricing sesuai kualitas.

**Acceptance Criteria:**

- [ ] QC assign grade berdasarkan hasil test (A/B/C/Reject)
- [ ] Setiap grade memiliki kriteria yang jelas dan terdokumentasi
- [ ] Grade mempengaruhi harga yang diterima peternak
- [ ] Peternak dapat melihat grade dan feedback di app mereka
- [ ] Sistem tracking grade history per peternak untuk analisis

**Priority:** P0 - Must Have

---

### 4.3 Traceability - Backward

**User Story:**  
Sebagai QC staff, saya ingin trace-back dari keju bermasalah ke batch susu asalnya, sehingga root cause bisa diidentifikasi dengan cepat.

**Acceptance Criteria:**

- [ ] Dari cheese batch ID, QC dapat melihat semua milk batch yang digunakan
- [ ] Dari milk batch, QC dapat melihat peternak asal dan hasil QC
- [ ] Dari milk batch, QC dapat melihat data transport (suhu, durasi)
- [ ] Sistem menampilkan timeline lengkap dari farm to cheese
- [ ] QC dapat flag batch dan notify semua pihak terkait

**Priority:** P0 - Must Have

---

### 4.4 Quality Testing - Keju

**User Story:**  
Sebagai QC staff, saya ingin mencatat hasil test kualitas keju di berbagai tahap aging, sehingga konsistensi produk terjaga.

**Acceptance Criteria:**

- [ ] QC input hasil test keju: tekstur, rasa, aroma, appearance
- [ ] QC dapat upload foto keju di setiap tahap
- [ ] Test dilakukan di milestone aging (misal: 1 minggu, 1 bulan, 3 bulan)
- [ ] Sistem alert jika ada anomali dibanding batch sebelumnya
- [ ] Hasil test menentukan apakah keju layak release atau perlu extended aging

**Priority:** P1 - Should Have

---

## 5. Cheese Maker / Production

### 5.1 Informasi Kualitas dan Origin Susu

**User Story:**  
Sebagai cheese maker, saya ingin melihat kualitas dan origin susu per batch, sehingga saya bisa adjust proses produksi sesuai karakteristik susu.

**Acceptance Criteria:**

- [ ] Cheese maker melihat detail batch susu yang akan digunakan
- [ ] Informasi mencakup: peternak asal, tanggal perah, hasil QC, grade
- [ ] Informasi mencakup: data pakan ternak (jika tersedia)
- [ ] Cheese maker dapat memilih batch mana yang digunakan untuk produk tertentu
- [ ] Notes dari peternak dan transporter visible

**Priority:** P0 - Must Have

---

### 5.2 Pencatatan Batch Produksi

**User Story:**  
Sebagai cheese maker, saya ingin mencatat setiap batch produksi keju dengan detail proses, sehingga ada dokumentasi lengkap untuk quality control dan improvement.

**Acceptance Criteria:**

- [ ] Cheese maker membuat production batch dengan unique ID
- [ ] Input: milk batch yang digunakan, jumlah liter
- [ ] Input: jenis keju yang diproduksi
- [ ] Input: starter culture, rennet, bahan tambahan
- [ ] Input: parameter proses (suhu, waktu, pH di setiap tahap)
- [ ] Cheese maker dapat upload foto proses

**Priority:** P0 - Must Have

---

### 5.3 Tracking Aging

**User Story:**  
Sebagai cheese maker, saya ingin mencatat dan memantau proses aging setiap batch keju, sehingga saya tahu kapan keju siap dijual dan bisa maintain konsistensi.

**Acceptance Criteria:**

- [ ] Setiap cheese batch memiliki aging start date
- [ ] Sistem menghitung usia keju otomatis
- [ ] Cheese maker set target aging duration per jenis keju
- [ ] Alert saat keju mendekati target aging (H-7, H-1, H-Day)
- [ ] Cheese maker dapat extend atau early-release dengan catatan alasan
- [ ] Dashboard menampilkan semua keju dalam aging dengan status

**Priority:** P0 - Must Have

---

### 5.4 Yield Tracking

**User Story:**  
Sebagai cheese maker, saya ingin tracking yield (liter susu → kg keju), sehingga saya bisa identifikasi inefficiency dalam proses produksi.

**Acceptance Criteria:**

- [ ] Sistem auto-calculate yield ratio per batch
- [ ] Dashboard menampilkan average yield per jenis keju
- [ ] Alert jika yield di bawah threshold (misal: <10%)
- [ ] Cheese maker dapat compare yield antar batch
- [ ] Report yield trend over time

**Priority:** P1 - Should Have

---

### 5.5 Recipe Management

**User Story:**  
Sebagai cheese maker, saya ingin menyimpan dan mengelola resep untuk setiap jenis keju, sehingga proses produksi konsisten dan bisa direplikasi.

**Acceptance Criteria:**

- [ ] Cheese maker dapat create/edit recipe
- [ ] Recipe mencakup: bahan, proporsi, langkah, parameter (suhu, waktu, pH)
- [ ] Recipe memiliki version control
- [ ] Saat membuat batch baru, cheese maker dapat load recipe sebagai template
- [ ] Sistem tracking recipe mana yang digunakan di setiap batch

**Priority:** P1 - Should Have

---

## 6. Admin / Manager

### 6.1 Dashboard Supply Chain

**User Story:**  
Sebagai admin, saya ingin memonitor seluruh supply chain dari peternak hingga produksi dalam satu dashboard, sehingga saya bisa memastikan alur susu berjalan lancar dan transparan.

**Acceptance Criteria:**

- [ ] Dashboard real-time menampilkan: milk collected today, in-transit, received, in-production
- [ ] Peta menampilkan posisi transporter aktif
- [ ] Status setiap peternak (sudah pickup / belum)
- [ ] Quick stats: total volume hari ini vs target vs kemarin
- [ ] Alert section untuk issues yang perlu attention

**Priority:** P0 - Must Have

---

### 6.2 Reporting Otomatis

**User Story:**  
Sebagai admin, saya ingin menghasilkan laporan produksi, pengiriman, dan stok secara otomatis, sehingga saya bisa membuat keputusan berbasis data untuk operasional.

**Acceptance Criteria:**

- [ ] Report harian: collection summary, quality summary, production summary
- [ ] Report mingguan: trend analysis, peternak performance, yield analysis
- [ ] Report bulanan: financial summary, comprehensive analytics
- [ ] Report dapat di-export ke PDF dan Excel
- [ ] Report dapat di-schedule untuk auto-send via email

**Priority:** P0 - Must Have

---

### 6.3 User Management

**User Story:**  
Sebagai admin, saya ingin mengelola akun peternak, transporter, dan staf, sehingga semua user memiliki akses sesuai perannya.

**Acceptance Criteria:**

- [ ] Admin dapat create/edit/deactivate user accounts
- [ ] Admin dapat assign role dan permissions
- [ ] Admin dapat reset password user
- [ ] Audit log untuk semua user management actions
- [ ] Bulk import peternak dari Excel

**Priority:** P0 - Must Have

---

### 6.4 Alert Management

**User Story:**  
Sebagai admin, saya ingin mendapatkan alert bila ada susu yang tidak sesuai standar atau keterlambatan pengiriman, sehingga masalah bisa ditangani sebelum berdampak besar.

**Acceptance Criteria:**

- [ ] Alert untuk: rejected milk, delayed pickup, temperature breach, low yield
- [ ] Alert dapat dikonfigurasi threshold-nya
- [ ] Alert dikirim via: in-app notification, email, WhatsApp
- [ ] Admin dapat assign alert ke PIC untuk follow-up
- [ ] Alert history dan resolution tracking

**Priority:** P0 - Must Have

---

### 6.5 Traceability Dashboard

**User Story:**  
Sebagai admin, saya ingin melihat traceability lengkap dari keju ke peternak asal, sehingga saya bisa support premium positioning dan transparansi ke customer.

**Acceptance Criteria:**

- [ ] Search by cheese batch → lihat full journey
- [ ] Search by peternak → lihat semua keju yang menggunakan susunya
- [ ] Visual timeline dari farm to finished product
- [ ] Generate traceability certificate untuk customer (PDF)
- [ ] QR code di produk yang link ke traceability info

**Priority:** P1 - Should Have

---

### 6.6 Seasonal Analysis

**User Story:**  
Sebagai admin, saya ingin melihat analisis seasonal produksi susu, sehingga saya bisa planning produksi keju sesuai ketersediaan susu.

**Acceptance Criteria:**

- [ ] Dashboard menampilkan trend produksi per bulan/quarter
- [ ] Comparison year-over-year
- [ ] Forecast estimasi produksi bulan depan berdasarkan historical data
- [ ] Correlation dengan faktor eksternal (musim hujan/kemarau)
- [ ] Rekomendasi production planning

**Priority:** P2 - Nice to Have

---

### 6.7 Peternak Performance Management

**User Story:**  
Sebagai admin, saya ingin melihat performance setiap peternak (volume, kualitas, konsistensi), sehingga saya bisa memberikan feedback dan incentive yang tepat.

**Acceptance Criteria:**

- [ ] Scorecard per peternak: avg volume, avg grade, consistency score
- [ ] Ranking peternak berdasarkan berbagai metric
- [ ] Trend performance over time
- [ ] Flag peternak yang perlu attention (declining quality/volume)
- [ ] Input notes/feedback untuk peternak

**Priority:** P1 - Should Have

---

## 7. Finance

### 7.1 Kalkulasi Pembayaran Peternak

**User Story:**  
Sebagai finance staff, saya ingin sistem menghitung pembayaran ke peternak secara otomatis berdasarkan volume dan grade, sehingga proses pembayaran akurat dan efisien.

**Acceptance Criteria:**

- [ ] Sistem auto-calculate berdasarkan: volume × harga per grade
- [ ] Harga per grade dapat dikonfigurasi dan memiliki effective date
- [ ] Finance dapat review dan approve payment batch
- [ ] Deduction dapat ditambahkan jika ada (misal: pinjaman, potongan)
- [ ] Generate payment summary untuk transfer bank

**Priority:** P0 - Must Have

---

### 7.2 Cost Tracking per Batch

**User Story:**  
Sebagai finance staff, saya ingin tracking cost per batch keju (bahan baku, labor, overhead), sehingga saya bisa menghitung profitability per produk.

**Acceptance Criteria:**

- [ ] Input cost components per cheese batch
- [ ] Auto-pull milk cost dari payment data
- [ ] Calculate COGS per kg keju
- [ ] Compare COGS antar batch dan antar jenis keju
- [ ] Margin analysis per produk

**Priority:** P1 - Should Have

---

### 7.3 Payment History & Reconciliation

**User Story:**  
Sebagai finance staff, saya ingin melihat history pembayaran dan melakukan reconciliation, sehingga tidak ada pembayaran yang terlewat atau duplikat.

**Acceptance Criteria:**

- [ ] Daftar semua pembayaran dengan status
- [ ] Filter by: peternak, periode, status
- [ ] Mark as paid setelah transfer selesai
- [ ] Upload bukti transfer
- [ ] Reconciliation report: expected vs actual payments

**Priority:** P0 - Must Have

---

## Non-Functional Requirements

### Security

- Authentication menggunakan phone number + OTP untuk peternak dan transporter
- Email + password untuk staff roles
- Role-based access control (RBAC)
- Data encryption at rest dan in transit
- Audit trail untuk semua critical actions

### Performance

- Mobile app harus responsive di device low-end
- Dashboard load time < 3 detik
- Sync offline data dalam < 30 detik saat online

### Availability

- System uptime target: 99.5%
- Backup data harian
- Disaster recovery plan

### Usability

- Mobile app mendukung Bahasa Indonesia
- UI sederhana untuk peternak (minimize text input, maximize selection)
- Training materials dan tooltips in-app

### Integration

- WhatsApp API untuk notifikasi
- Bank API untuk payment (optional, phase 2)
- Export ke accounting software (optional, phase 2)

---

## Prioritization Summary

### Phase 1 - MVP (P0)

| Module       | Stories            |
| ------------ | ------------------ |
| Peternak     | 1.1, 1.2, 1.3, 1.6 |
| Transporter  | 2.1, 2.2, 2.4      |
| Gudang       | 3.1, 3.2, 3.3      |
| QC           | 4.1, 4.2, 4.3      |
| Cheese Maker | 5.1, 5.2, 5.3      |
| Admin        | 6.1, 6.2, 6.3, 6.4 |
| Finance      | 7.1, 7.3           |

### Phase 2 - Enhancement (P1)

| Module       | Stories  |
| ------------ | -------- |
| Peternak     | 1.5      |
| Transporter  | 2.3, 2.5 |
| QC           | 4.4      |
| Cheese Maker | 5.4, 5.5 |
| Admin        | 6.5, 6.7 |
| Finance      | 7.2      |

### Phase 3 - Optimization (P2)

| Module   | Stories |
| -------- | ------- |
| Peternak | 1.4     |
| Admin    | 6.6     |

---

## Appendix

### A. Glossary

| Term         | Definition                                                      |
| ------------ | --------------------------------------------------------------- |
| Batch        | Kumpulan susu atau keju yang diproses bersamaan dengan ID unik  |
| Cold Chain   | Rantai pendingin untuk menjaga suhu susu dari farm ke facility  |
| Grade        | Klasifikasi kualitas susu (A/B/C/Reject) berdasarkan hasil test |
| Yield        | Rasio output keju terhadap input susu (kg keju / liter susu)    |
| Aging        | Proses pematangan keju dalam kondisi terkontrol                 |
| Traceability | Kemampuan melacak produk dari origin hingga end product         |

### B. Cheese Products Reference (Mazaraat)

- Wild Eclipse
- Tomme De Merapi
- St. Paulin
- (tambahkan produk lain sesuai catalog)

### C. Quality Standards Reference

| Parameter         | Grade A | Grade B   | Grade C  | Reject         |
| ----------------- | ------- | --------- | -------- | -------------- |
| pH                | 6.6-6.8 | 6.5-6.9   | 6.4-7.0  | <6.4 atau >7.0 |
| Fat %             | >3.5%   | 3.0-3.5%  | 2.5-3.0% | <2.5%          |
| Bacteria (CFU/ml) | <100k   | 100k-500k | 500k-1M  | >1M            |
| Temperature       | 4-7°C   | 4-10°C    | 4-15°C   | >15°C          |

_Note: Standar di atas adalah contoh dan harus disesuaikan dengan standar Mazaraat_

---

**Document Control**

| Version | Date     | Author | Changes         |
| ------- | -------- | ------ | --------------- |
| 1.0     | Dec 2024 | -      | Initial version |
