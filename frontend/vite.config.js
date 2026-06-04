import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],

  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },

  build: {
    // Target modern browsers (drop legacy IE support)
    target: 'es2020',

    // Warn when a single chunk exceeds 1 MB
    chunkSizeWarningLimit: 1000,

    // Clean output directory before each build
    emptyOutDir: true,

    // Enable CSS code splitting per chunk
    cssCodeSplit: true,

    // Source maps only for staging — disable for production to reduce size
    sourcemap: false,

    rollupOptions: {
      output: {
        // Content-hash filenames for long-lived browser caching
        entryFileNames: 'assets/[name].[hash].js',
        chunkFileNames: 'assets/[name].[hash].js',
        assetFileNames: 'assets/[name].[hash].[ext]',

        // Manual chunk splitting to optimise loading
        // NOTE: rolldown (Vite 8) requires manualChunks to be a function, not a plain object.
        manualChunks: (id) => {
          if (id.includes('node_modules')) {
            if (id.includes('vue') || id.includes('vue-router') || id.includes('pinia')) {
              return 'vendor-vue';
            }
            if (id.includes('chart.js') || id.includes('vue-chartjs')) {
              return 'vendor-charts';
            }
            if (id.includes('jspdf')) {
              return 'vendor-pdf';
            }
            if (id.includes('bootstrap') || id.includes('@popperjs')) {
              return 'vendor-bootstrap';
            }
            if (id.includes('axios')) {
              return 'vendor-axios';
            }
          }
        },
      },
    },
  },

  // Preview server (vite preview) — mirrors production paths
  preview: {
    port: 4173,
    strictPort: true,
  },
})
