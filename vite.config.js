import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';
import path from 'path';
import fs from 'fs';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js'],
      refresh: true,
    }),
    vue(),
    VitePWA({
      registerType: 'autoUpdate',
      outDir: 'public',
      base: '/',
      devOptions: {
        enabled: true,
        type: 'module',
        navigateFallback: '/',
      },
      workbox: {
        globPatterns: ['**/*.{js,css,ico,png,svg,woff,woff2}'], // Excluye html
        cleanupOutdatedCaches: true,
        navigateFallback: '/',
        navigateFallbackAllowlist: [/^\/.*/],
        // Ignora la ruta raíz para precacheo
        globIgnores: ['index.html', '/'],
        // Cacheo dinámico para rutas de Laravel
        runtimeCaching: [
          {
            urlPattern: /^https:\/\/project\.test\/.*/,
            handler: 'NetworkFirst',
            options: {
              cacheName: 'dynamic',
              expiration: {
                maxEntries: 50,
                maxAgeSeconds: 30 * 24 * 60 * 60, // 30 días
              },
            },
          },
        ],
      },
      includeAssets: ['192.png', '512.png', 'robots.txt'],
      manifest: {
        name: 'Win to Win Jobs',
        short_name: 'W2W',
        start_url: '/',
        display: 'standalone',
        background_color: '#ffffff',
        theme_color: '#ffffff',
        orientation: 'portrait',
        scope: '/',
        icons: [
          {
            src: '/192.png',
            sizes: '192x192',
            type: 'image/png',
          },
          {
            src: '/512.png',
            sizes: '512x512',
            type: 'image/png',
          },
        ],
      },
    }),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
    },
  },
  server: {
    https: {
      key: fs.readFileSync('./certs/project.test-key.pem'),
      cert: fs.readFileSync('./certs/project.test.pem'),
    },
    host: 'project.test',
    port: 5173,
  },
});