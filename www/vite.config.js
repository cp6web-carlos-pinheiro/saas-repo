import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const hmrHost = process.env.VITE_HMR_HOST;
const hmrClientPort = Number(process.env.VITE_HMR_CLIENT_PORT ?? 5173);
const appOrigin = new URL(process.env.APP_URL ?? 'http://localhost:8000').origin;
const appOriginUrl = new URL(appOrigin);
const alternateAppHost = appOriginUrl.hostname === 'localhost' ? '127.0.0.1' : 'localhost';
const alternateAppOrigin = `${appOriginUrl.protocol}//${alternateAppHost}${appOriginUrl.port ? `:${appOriginUrl.port}` : ''}`;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '127.0.0.1',
        origin: process.env.VITE_DEV_SERVER_URL,
        cors: {
            origin: [appOrigin, alternateAppOrigin],
            credentials: true,
        },
        hmr: hmrHost
            ? {
                host: hmrHost,
                clientPort: hmrClientPort,
            }
            : undefined,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
