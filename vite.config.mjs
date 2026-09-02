import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [tailwindcss()],
    server: {
        host: "127.0.0.1",
        port: 5173,
        strictPort: true,
        origin: "http://127.0.0.1:5173",
        cors: {
            origin: "http://calculadora.test",
        },
    },
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
