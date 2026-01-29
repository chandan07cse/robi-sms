import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
  plugins: [vue()],
  build: {
    outDir: 'public',
    emptyOutDir: true,
    rollupOptions: {
      input: resolve(__dirname, 'resources/js/main.js'),
      output: {
        entryFileNames: 'dashboard.js',
        chunkFileNames: 'chunks/[name]-[hash].js',
        assetFileNames: 'assets/[name]-[hash][extname]'
      }
    }
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js')
    }
  }
});
