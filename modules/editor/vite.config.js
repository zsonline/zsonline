import { dirname, resolve } from "path";
import { fileURLToPath } from "url";

import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";

const __dirname = dirname(fileURLToPath(import.meta.url));

export default defineConfig(({ command }) => ({
  base: command === "serve" ? "" : "/dist/",
  build: {
    emptyOutDir: true,
    manifest: true,
    outDir: resolve(__dirname, "src/assets/field/dist"),
    sourcemap: true,
    rollupOptions: {
      input: {
        index: resolve(__dirname, "src/assets/field/src/index.js"),
      },
    },
  },
  plugins: [vue()],
  resolve: {
    alias: {
      "@": resolve(__dirname, "src/assets/field/src"),
      "@components": resolve(__dirname, "src/assets/field/src/components"),
      "@composables": resolve(__dirname, "src/assets/field/src/composables"),
      "@extensions": resolve(__dirname, "src/assets/field/src/extensions"),
    },
  },
  root: resolve(__dirname, "src/assets"),
  server: {
    host: "0.0.0.0",
    port: 5173,
    strictPort: true,
  },
}));
