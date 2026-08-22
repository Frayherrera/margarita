import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Public Sans"', ...defaultTheme.fontFamily.sans],
                display: ['"Alfa Slab One"', 'serif'],
                mono: ['"IBM Plex Mono"', 'ui-monospace', 'monospace'],
            },
            colors: {
                tinta: '#26180D',
                caoba: '#5f4383',
                crema: '#F3EAD8',
                ambar: '#E09A32',
                billete: '#1F6F50',
                ladrillo: '#B4472E',
            },
        },
    },

    plugins: [forms],
};
