import { writeFileSync } from 'fs';
import { resolve } from 'path';

// Contenido básico para sw.js en modo desarrollo (sin Workbox)
const swContent = `
  self.addEventListener('install', (event) => {
    console.log('[Service Worker] Instalado');
    self.skipWaiting();
  });

  self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activado');
  });

  self.addEventListener('fetch', (event) => {
    event.respondWith(
      fetch(event.request).catch(() => {
        return new Response('Offline', { status: 503 });
      })
    );
  });
`;

// Ruta donde se escribirá sw.js
const outputPath = resolve('public/sw.js');
writeFileSync(outputPath, swContent, 'utf-8');
console.log(`✅ sw.js generado en ${outputPath}`);