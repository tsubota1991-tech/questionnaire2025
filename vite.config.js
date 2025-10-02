// vite.config.js
import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');

  // IPv6の [::1] ではなく 127.0.0.1 を既定にする（必要なら .env で上書き）
  const host = env.VITE_HMR_HOST || '127.0.0.1';
  const port = Number(env.VITE_PORT || 5173);
  const protocol = env.VITE_HMR_PROTOCOL || 'ws';

  return {
    plugins: [
      laravel({
        input: [
          'resources/css/app.css',
          'resources/js/app.js',
          'resources/js/admin/questions-create.js',
        ],
        refresh: true,
      }),
      tailwindcss(),
    ],
    resolve: {
      alias: { '@': '/resources/js' },
    },
    server: {
      host: env.VITE_SERVER_HOST || true, // true で 0.0.0.0 / :: 受け入れ
      port,
      strictPort: true,
      hmr: {
        host,        // 既定 127.0.0.1（.env で変更可）
        port,        // 既定 5173
        protocol,    // 既定 ws
      },
    },
    preview: {
      host: env.VITE_SERVER_HOST || true,
      port: Number(env.VITE_PREVIEW_PORT || port + 1),
    },
  };
});
