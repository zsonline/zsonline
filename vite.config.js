import { resolve } from "path";

import { defineConfig } from "vite";
import ViteRestart from "vite-plugin-restart";

export default defineConfig(({ command }) => ({
  base: command === "serve" ? "" : "/dist/",
  build: {
    emptyOutDir: true,
    manifest: true,
    outDir: resolve(import.meta.dirname, "web/dist"),
    rollupOptions: {
      input: {
        scripts: resolve(import.meta.dirname, "assets/scripts/main.js"),
        styles: resolve(import.meta.dirname, "assets/styles/main.scss"),
      },
    },
  },
  plugins: [
    ViteRestart({
      reload: ["templates/**/*.twig"],
    }),
  ],
  server: {
    host: "0.0.0.0",
    port: 5173,
    strictPort: true,
    allowedHosts: ["zs.ddev.site", "www.zs.ddev.site"],
    cors: {
      origin: /^https:\/\/(?:www\.)?zs\.ddev\.site$/,
    },
  },
}));
