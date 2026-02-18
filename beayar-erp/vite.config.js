import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js', 'resources/css/app.css', 'resources/js/quotations/show.js'],
            refresh: true,
        }),
    ],
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('suneditor') || id.includes('katex')) {
                            return 'editor-vendor';
                        }
                        if (id.includes('apexcharts')) {
                            return 'charts-vendor';
                        }
                        if (id.includes('exceljs')) {
                            return 'excel-vendor';
                        }
                        
                        return 'vendor';
                    }
                }
            }
        }
    }
});
