import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [tailwindcss()],
    build: {
        rollupOptions: {
            input: "src/main.js",
            output: {
                entryFileNames: "assets/main.js",
                assetFileNames: "assets/style[extname]",
            },
        },
        outDir: "dist",
        emptyOutDir: true,
    },
});