import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    outDir: 'public/css',
    emptyOutDir: false,
    rollupOptions: {
      input: {
        app: resolve(__dirname, 'resources/css/app.css'),
      },
      output: {
        assetFileNames: '[name][extname]',
      },
    },
  },
});
