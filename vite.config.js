import { defineConfig, splitVendorChunkPlugin } from 'vite';
import vue from '@vitejs/plugin-vue';
import liveReload from 'vite-plugin-live-reload';
import path from 'path';
import fs from 'fs';

const rootpath = './resources/scripts';
const themeDirName = path.basename(__dirname);

// Read from files from rootPath
function getTopLevelFiles() {
  let topLevelFiles = fs.readdirSync(path.resolve(__dirname, rootpath));
  let files = {};
  topLevelFiles.forEach((file) => {
    const isFile = fs.lstatSync(path.resolve(rootpath, file)).isFile();
    if (isFile && !file.includes('.d.ts')) {
      const chunkName = file.slice(0, file.lastIndexOf('.'));
      files[chunkName] = path.resolve(rootpath, file);
    }
  });
  return files;
}

export default defineConfig({
  root: rootpath,
  base: process.env.APP_VITE_ENV === 'dev' ? '/' : `/app/themes/${themeDirName}/dist/`,
  build: {
    manifest: true,
    emptyOutDir: true,
    outDir: path.resolve(__dirname, 'dist'),
    assetsDir: '',
    rollupOptions: {
      input: getTopLevelFiles(),
    },
  },
  sourcemap: true,
  server: {
    // required to load scripts from custom host
    cors: true,
    strictPort: true,
    port: 3000,
    hmr: {
      port: 3000,
      host: 'localhost',
      protocol: 'ws',
    },
  },

  plugins: [
    vue(),
    liveReload(`${__dirname}/**/*\.php`),
    splitVendorChunkPlugin(),
  ],

  // required for in-browser template compilation
  // https://vuejs.org/guide/scaling-up/tooling.html#note-on-in-browser-template-compilation
  resolve: {
    alias: {
      vue: 'vue/dist/vue.esm-bundler.js',
      '@': path.resolve(__dirname, 'resources'),
    }
  }
});