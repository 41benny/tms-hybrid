import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        // Minify with esbuild for faster builds
        minify: 'esbuild',
        
        // Target modern browsers for smaller bundles
        target: 'es2020',
        
        // Code splitting optimization
        rollupOptions: {
            output: {
                manualChunks: {
                    // Split vendor code into separate chunk
                    vendor: ['axios'],
                },
                // Use content-based hash for better caching
                chunkFileNames: 'js/[name]-[hash].js',
                entryFileNames: 'js/[name]-[hash].js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name && assetInfo.name.endsWith('.css')) {
                        return 'css/[name]-[hash][extname]';
                    }
                    return 'assets/[name]-[hash][extname]';
                },
            },
        },
        
        // Optimize chunk size
        chunkSizeWarningLimit: 1000,
        
        // Source maps for production debugging (optional, remove if not needed)
        sourcemap: false,
        
        // CSS code splitting
        cssCodeSplit: true,
        
        // Optimize assets
        assetsInlineLimit: 4096, // Inline assets < 4kb as base64
    },
    
    // Server optimization for development
    server: {
        hmr: {
            overlay: true,
        },
    },
    
    // Optimize dependencies pre-bundling
    optimizeDeps: {
        include: ['axios'],
        exclude: [],
    },
});
