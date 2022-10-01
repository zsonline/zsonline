import { dirname, resolve } from "path";
import { fileURLToPath } from "url";

import { defineConfig } from "vite";
import ViteRestart from "vite-plugin-restart";

const __dirname = dirname(fileURLToPath(import.meta.url));

export default defineConfig(({ command }) => ({
  base: command === "serve" ? "" : "/dist/",
  build: {
    emptyOutDir: true,
    manifest: true,
    outDir: resolve(__dirname, "web/dist"),
    rollupOptions: {
      input: {
        scripts: resolve(__dirname, "assets/scripts/main.js"),
        styles: resolve(__dirname, "assets/styles/main.scss"),
      },
    },
  },
  plugins: [
    ViteRestart({
      reload: ["templates/**/*.twig"],
    }),
  ],
  resolve: {
    alias: {
      "@": resolve(__dirname, "assets"),
      "@fonts": resolve(__dirname, "assets/fonts"),
      "@images": resolve(__dirname, "assets/images"),
      "@node_modules": resolve(__dirname, "node_modules"),
      "@scripts": resolve(__dirname, "assets/scripts"),
      "@styles": resolve(__dirname, "assets/styles"),
    },
  },
  server: {
    host: "0.0.0.0",
    port: 5173,
    strictPort: true,
  },
}));
