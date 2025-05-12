import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js'], // o 'app.ts' si usas TypeScript
      refresh: true,
    }),
    vue(),
  ],
});
