// @ts-check
import { defineConfig, envField } from 'astro/config';
import tailwindcss from '@tailwindcss/vite';
import react from '@astrojs/react';
import alpinejs from "@astrojs/alpinejs";


// https://astro.build/config
export default defineConfig({
  output: 'static',
  base: "/landingform/plan1/",
  i18n: {
    defaultLocale: "es",
    locales: ["es", "en", "pt"],
    routing: {
      prefixDefaultLocale: true,
      redirectToDefaultLocale: false
    },
    fallback: {
      en: "es"
    }
  },
  vite: {
    plugins: [tailwindcss()],
  },
  integrations: [react(), alpinejs()],
});