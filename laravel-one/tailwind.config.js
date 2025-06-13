const defaultTheme = require('@abenevaut/tailwindui/tailwind.config.js')

/** @type {import('tailwindcss').Config} */
export default {
  ...defaultTheme,
  content: [
    "./theme/**/*.jsx",
    "./theme/**/*.blade.php",
    "node_modules/@abenevaut/tailwindui/src/js/**/*.jsx",
  ],
};
