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
            const htaccessPath = resolve(outDir, '.htaccess');

            await copyFile(indexPath, fallbackPath);
            await writeFile(redirectsPath, '/* /index.html 200\n');
            await writeFile(
                htaccessPath,
                `Options -Indexes\n` +
                    `<IfModule mod_rewrite.c>\n` +
                    `RewriteEngine On\n` +
                    `RewriteCond %{REQUEST_FILENAME} !-f\n` +
                    `RewriteCond %{REQUEST_FILENAME} !-d\n` +
                    `RewriteRule ^ index.html [L]\n` +
                    `</IfModule>\n`
            );
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
