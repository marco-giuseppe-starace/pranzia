import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    // Genera manifest.json e service worker per rendere l'app installabile
    // come PWA sul telefono del cliente, senza bisogno di app store.
    VitePWA({
      registerType: 'autoUpdate',
      manifest: {
        name: 'Pranzia',
        short_name: 'Pranzia',
        description: 'Ordina comodamente al tavolo grazie all\'IA',
        theme_color: '#EF9F27',
        background_color: '#EF9F27',
        display: 'standalone',
        icons: [
          {
            src: '/pranzia-icon-192.png',
            sizes: '192x192',
            type: 'image/png',
          },
          {
            src: '/pranzia-icon-512.png',
            sizes: '512x512',
            type: 'image/png',
          },
        ],
      },
    }),
  ],
  server: {
    // In sviluppo il frontend gira su Vite, ma le chiamate /api devono
    // raggiungere il backend Laravel (php artisan serve).
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
      },
      // Immagini caricate dall'admin (menu_items.image_url), servite dal
      // backend via storage:link.
      '/storage': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
      },
    },
    // Permette l'accesso tramite il tunnel Cloudflare (trycloudflare.com)
    // usato per testare l'app da telefono quando il firewall aziendale
    // blocca le connessioni dirette sulla LAN.
    allowedHosts: ['.trycloudflare.com'],
  },
})
