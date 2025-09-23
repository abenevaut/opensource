import { globSync } from 'glob';
import path from 'path';
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';

const isProductionEnvironment = 'production' === process.env.NODE_ENV;

export default defineConfig({
  base: isProductionEnvironment
      ? '/'
      : '/dev.abenevaut/abenevaut/dist/',
  build: {
    manifest: true,
    sourcemap: !isProductionEnvironment,
    minify: isProductionEnvironment,
    css: {
      minify: isProductionEnvironment,
    },
    outDir: path.join(__dirname, 'dist'),
    rollupOptions: {
      output: {
        entryFileNames: `assets/[name].js`,
        chunkFileNames: `assets/[name].js`,
        assetFileNames: `assets/[name].[ext]`,
      },
    },
  },
  plugins: [
    react(),
    laravel({
      input: [
        'theme/css/app.css',
        ...(globSync('theme/js/*.jsx')
                .filter(function(item) {
                  return !item.includes('AppNavigation.jsx');
                })
        ),
      ],
      refresh: true,
    }),
  ],
});
