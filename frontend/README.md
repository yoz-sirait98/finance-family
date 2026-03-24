# Aplikasi Web Frontend Family Finance

Direktori ini berisi dashboard **Single Page Application (SPA)** untuk sistem Family Finance, yang dibangun menggunakan **Vue 3 (Composition API)** dan **Vite**.

## Technology Stack
*   **Framework**: Vue 3 (Script Setup syntax)
*   **Bundler**: Vite
*   **Routing**: Vue Router
*   **State Management**: Pinia
*   **HTTP Client**: Axios
*   **UI/Styling**: CSS standar yang memanfaatkan grid dan utility classes dari Bootstrap 5
*   **Data Visualization**: Chart.js yang dibungkus dengan `vue-chartjs`

## Setup Pengembangan Lokal

1.  **Install dependencies:**
    Pastikan Anda menggunakan versi Node.js terbaru (v18+).

    ```bash
    npm install
    ```

2.  **Pengaturan Environment:**
    Buat file `.env` pada root direktori `frontend/` untuk menunjuk ke Laravel Backend API.

    ```env
    VITE_API_URL=http://localhost:8000/api
    ```

3.  **Menjalankan dev server:**

    ```bash
    npm run dev
    ```

    Aplikasi biasanya akan berjalan di `http://localhost:5173`.

## Catatan Arsitektur & Desain

### Penanganan Event Native Vue
Aplikasi ini secara sengaja **tidak menggunakan Bootstrap Javascript** (`bootstrap.bundle.js` atau jQuery).

Karena Virtual DOM milik Vue mengontrol siklus render, penggunaan plugin JavaScript imperatif sering menyebabkan race condition dan event listener yang tertinggal ("zombie"). Semua modal, dropdown, alert, dan toast dalam aplikasi ini dibangun sepenuhnya menggunakan directive native Vue (`v-if`, `v-show`, `@click`, modifier `.self`) serta CSS kustom.

### Notifikasi Toast Real-Time
Umpan balik untuk operasi Create, Update, dan Delete disediakan melalui sistem **Toast Notification global** yang dibuat secara kustom.

Sistem ini dikelola secara terpusat oleh store Pinia `toast.js` dan komponen `AppToast.vue` pada layer root aplikasi.

### Global State (Pinia)
Pola standar yang digunakan di sini adalah:

- **State lokal komponen** untuk data form yang reaktif
- **Global state Pinia** khusus untuk:
  - Authentication (`auth.js`)
  - Notifikasi Toast real-time (`toast.js`)
  - State notifikasi peringatan anggaran/bell (`budget.js`)

Pemanggilan API dilakukan langsung di dalam komponen secara berurutan menggunakan layer service Axios yang telah diabstraksi (`frontend/src/services/*`). Fitur *Promise.all* diimplementasikan secara ekstensif pada rendering dashboard untuk mengeliminasi latensi.

### Eksport Laporan Client-Side
Fungsi unduh laporan analitik bulanan ke **PDF** (menggunakan `jsPDF` + `jspdf-autotable`) maupun **CSV** dilakukan murni eksplisit dengan 100% Javascript di sisi *browser* untuk mencegah kelebihan muatan di sisi *backend*. 

Pendekatannya menggunakan *Native File System Access API* (`window.showSaveFilePicker`), sehingga dapat membypass total ekstensi *Download Manager* (seperti IDM) yang kerap merusak dan membajak ekspor web menjadi *file random UUID Blob*. Hal ini menjamin file yang diunduh pasti mendarat secara sempurna sesuai namanya.

### Integritas Sistem & Visual Guardrail
Komponen transaksi dirancang sangat proaktif dalam menjaga ketertiban pencatatan finansial. Sebagai contoh, ada fitur interaktif *Budget Guardrail* – jika user akan memasukkan pengeluaran yang memecah sisa anggaran bulanannya, aplikasi akan memblokirnya dengan jendela *confirmation modal* mendetail dan mengekskalasi badge lonceng pergerakan sebelum database termutasi. Juga mendukung *Linked Sinking Fund* (mengikat target *goals* tabungan untuk mengambil sisa rasio *balance* langsung secara sinkronisasi *real-time* dari sub-akun bank fisik terkait).