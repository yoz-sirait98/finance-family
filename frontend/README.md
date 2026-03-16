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
  - Toast Notifications (`toast.js`)

Pemanggilan API dilakukan langsung di dalam komponen secara berurutan menggunakan layer service Axios yang telah diabstraksi (`frontend/src/services/*`).