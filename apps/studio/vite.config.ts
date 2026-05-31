import tailwindcss from '@tailwindcss/vite'
import react from '@vitejs/plugin-react'
import path from 'node:path'
import { defineConfig } from 'vite'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  resolve: {
    alias: {
      '@styles': path.resolve(__dirname, 'src/styles'),
    },
  },
  css: {
    preprocessorOptions: {
      scss: {
        // Allow `@use '@styles/tokens' as *` in future component SCSS modules.
        loadPaths: [path.resolve(__dirname, 'src/styles')],
      },
    },
  },
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8080',
        changeOrigin: true,
      },
      '/sitemap.xml': {
        target: 'http://localhost:8080',
        changeOrigin: true,
      },
      '/robots.txt': {
        target: 'http://localhost:8080',
        changeOrigin: true,
      },
    },
  },
})
