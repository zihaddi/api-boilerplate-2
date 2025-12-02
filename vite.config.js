import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        origin: process.env.REPL_SLUG 
            ? `https://${process.env.REPL_SLUG}.${process.env.REPL_OWNER}.repl.co` 
            : undefined,
        hmr: {
            host: process.env.REPL_SLUG 
                ? `${process.env.REPL_SLUG}.${process.env.REPL_OWNER}.repl.co` 
                : 'localhost',
            protocol: process.env.REPL_SLUG ? 'wss' : 'ws',
            clientPort: process.env.REPL_SLUG ? 443 : 5173,
        },
    },
});
