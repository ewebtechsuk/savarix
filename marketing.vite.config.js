import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const marketingRoot = resolve(__dirname, 'resources/js/marketing');

export default defineConfig({
    root: marketingRoot,
    base: './',
    plugins: [vue()],
    build: {
        outDir: resolve(__dirname, 'dist'),
        emptyOutDir: true,
    },
});
