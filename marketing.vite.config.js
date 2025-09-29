import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { copyFile, writeFile } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const marketingRoot = resolve(__dirname, 'resources/js/marketing');

function createSpaFallbacks() {
    return {
        name: 'create-spa-fallbacks',
        apply: 'build',
        async closeBundle() {
            const outDir = resolve(__dirname, 'dist');
            const indexPath = resolve(outDir, 'index.html');
            const fallbackPath = resolve(outDir, '404.html');
            const redirectsPath = resolve(outDir, '_redirects');

            await copyFile(indexPath, fallbackPath);
            await writeFile(redirectsPath, '/* /index.html 200\n');
        },
    };
}

export default defineConfig({
    root: marketingRoot,
    base: './',
    plugins: [vue(), createSpaFallbacks()],
    build: {
        outDir: resolve(__dirname, 'dist'),
        emptyOutDir: true,
    },
});
