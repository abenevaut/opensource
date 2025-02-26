/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: '#77BAA9',
                    300: '#85C1B2',
                    700: '#477065',
                },
            },
            fontFamily: {
                sans: [
                    'Rubik, sans-serif',
                ],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('tailwind-dracula')('dracula'),
    ],
}
