# Sistem Manajemen Keuangan Keluarga

Aplikasi pencatatan keuangan keluarga yang siap digunakan pada lingkungan produksi (production-ready). Sistem ini dirancang untuk digunakan oleh satu keluarga (menggunakan satu akun utama sebagai pusat) dengan beberapa anggota individu yang dapat mencatat pemasukan, pengeluaran, anggaran, target tabungan, dan transaksi berulang dalam satu dashboard terpadu.

Sistem ini menyediakan pencatatan akuntansi **dual-entry** untuk transfer antar akun serta **notifikasi real-time pada frontend** ketika penggunaan anggaran mendekati batas tertentu.

## Struktur Proyek

Repositori ini menggunakan struktur **monorepo** yang berisi aplikasi frontend dan backend:

*   [`/backend`](./backend) - REST API berbasis Laravel 11. Menangani seluruh penyimpanan data, logika bisnis, autentikasi, dan penjadwalan proses background.
*   [`/frontend`](./frontend) - Vue 3 Single Page Application (SPA). Antarmuka pengguna yang responsif dan dinamis yang dibangun menggunakan Vite, Vue Router, Pinia, dan Bootstrap.

## Arsitektur

*   **API Framework**: Laravel 11 
*   **Database**: MySQL 8
*   **Authentication**: Laravel Sanctum (berbasis token)
*   **Frontend Framework**: Vue 3 (Composition API)
*   **Build Tool**: Vite
*   **State Management**: Pinia
*   **Styling**: Custom CSS berbasis Bootstrap 5
*   **Charts**: Chart.js / Vue-Chartjs

## Fitur Utama

1.  **Manajemen Anggota**: Menambahkan anggota keluarga (Ayah, Ibu, Anak, dll.) dan mengaitkan transaksi kepada masing-masing anggota.
2.  **Manajemen Akun**: Melacak saldo pada kas, rekening bank, dan e-wallet.
3.  **Pencatatan Transaksi**: Mencatat pemasukan, pengeluaran, serta transfer antar akun keluarga menggunakan sistem dual-entry.
4.  **Anggaran (Budget)**: Menentukan batas pengeluaran bulanan per kategori dengan progress bar real-time serta **Budget Guardrail** (peringatan modal interaktif sebelum menyimpan transaksi yang melebihi sisa anggaran) dan notifikasi *bell* yang terupdate secara langsung.
5.  **Target Tabungan (Saving Goals)**: Melacak target dana. Kini dilengkapi fitur **Linked Goal Pockets** yang memungkinkan pengguna menghubungkan target tabungan langsung ke saldo akun rekening fisik (seperti Bank Jago Pocket) secara otomatis.
6.  **Transaksi Berulang (Recurring Transactions)**: Secara otomatis mencatat tagihan atau gaji yang berulang berdasarkan frekuensi mingguan, bulanan, atau tahunan.
7.  **Dashboard Analitik**: Menyediakan laporan visual berupa grafik batang pemasukan/pengeluaran, tren 6 bulan, dan diagram pie distribusi kategori. Terintegrasi dengan filter dinamis berdasarkan *Bulan*, *Tahun*, dan *Anggota Keluarga*.
8.  **Eksport Laporan (*Client-Side*)**: Fitur unduh laporan keuangan komprehensif ke format **CSV** dan **PDF** terstruktur yang di-generate murni dari aplikasi browser (tanpa membebani server) dan kebal terhadap bug ekstensi *Download Manager*.
9.  **Sistem Activity Log (Audit Trail)**: Merekam secara mendetail seluruh jejak aktivitas atau perubahan (*Create, Update, Delete*) pada seluruh modul utama beserta *snapshot* data sebelum-dan-sesudah perubahan.

## Roadmap Deployment

Aplikasi ini siap untuk dijalankan menggunakan Docker atau dideploy pada stack server LEMP/LAMP standar. Silakan merujuk pada file README khusus di direktori `/backend` dan `/frontend` untuk panduan lengkap setup lokal.

## Tangkapan Layar (Screenshots)

Berikut adalah beberapa tampilan halaman dari aplikasi:

### 1. Dashboard Utama
![Dashboard Utama](docs/screenshots/page_dashboard_1773716785210.png)

### 2. Transaksi (Transactions)
![Halaman Transaksi](docs/screenshots/page_transactions_1773716798644.png)

### 3. Anggaran (Budgets)
![Halaman Anggaran](docs/screenshots/page_budgets_1773716816705.png)

### 4. Target Tabungan (Goals)
![Halaman Target Tabungan](docs/screenshots/page_goals_1773716819480.png)

### 5. Laporan Analitik (Reports)
![Halaman Laporan](docs/screenshots/page_reports_1773716836363.png)

### 6. Transaksi Berulang (Recurring)
![Halaman Transaksi Berulang](docs/screenshots/page_recurring_1773716827633.png)

### 7. Kelola Anggota Kawanan (Members)
![Halaman Anggota Keluarga](docs/screenshots/page_members_1773716830526.png)

### 8. Rekening & Dompet (Accounts)
![Halaman Akun](docs/screenshots/page_accounts_1773716805706.png)

### 9. Kategori Pemasukan & Pengeluaran (Categories)
![Halaman Kategori](docs/screenshots/page_categories_1773716814084.png)

### 10. Pengaturan Profil (Settings)
![Halaman Pengaturan](docs/screenshots/page_settings_1773716844257.png)