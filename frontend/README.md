# Family Finance Frontend Web App

This directory contains the Single Page Application (SPA) dashboard for the Family Finance system, built with **Vue 3 (Composition API)** and **Vite**.

## Technology Stack
*   **Framework**: Vue 3 (Script Setup syntax)
*   **Bundler**: Vite
*   **Routing**: Vue Router
*   **State Management**: Pinia
*   **HTTP Client**: Axios
*   **UI/Styling**: Standard CSS leveraging Bootstrap 5 grid/utility classes
*   **Data Visualization**: Chart.js wrapped with `vue-chartjs`

## Local Development Setup

1.  **Install dependencies:**
    Ensure you are using a recent version of Node.js (v18+).
    ```bash
    npm install
    ```
2.  **Environment Settings:**
    Create a `.env` file at the root of `frontend/` to point to the Laravel Backend API.
    ```env
    VITE_API_URL=http://localhost:8000/api
    ```
3.  **Start the dev server:**
    ```bash
    npm run dev
    ```
    The application will usually boot at `http://localhost:5173`.

## Architecture & Design Notes

### Vue-Native Event Handling
This application intentionally **does not use Bootstrap Javascript** (`bootstrap.bundle.js` or jQuery). 

Because Vue's Virtual DOM controls the rendering lifecycle, using imperative vanilla JS plugins often leads to race conditions and "zombie" event listeners. All modals, dropdowns, alerts, and toasts inside this application are built entirely with native Vue directives (`v-if`, `v-show`, `@click`, `.self` modifiers) and custom CSS.

### Real-Time Toast Notifications
Feedback for Create, Update, and Delete operations is provided by a global, custom Toast Notification system, managed centrally by the `toast.js` Pinia store and the `AppToast.vue` component at the root layer.

### Global State (Pinia)
The standard pattern used here involves local component state for reactive form data, but global Pinia store state specifically for Authentication (`auth.js`) and Toasts (`toast.js`). API calls happen sequentially within the components themselves using abstracted Axios service layers (`frontend/src/services/*`).
