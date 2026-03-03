import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                brand: ['Tilda Sans', 'Inter', ...defaultTheme.fontFamily.sans],
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                rosatom: {
                    900: '#001a3f',
                    800: '#003274',
                    700: '#003d8f',
                    600: '#025EA1',
                    500: '#0073c4',
                    400: '#6CACE4',
                    300: '#92c4ed',
                    200: '#bddaf4',
                    100: '#deedfb',
                    50: '#f0f6fd',
                },
                accent: {
                    yellow: '#FCC30B',
                    orange: '#FD6925',
                    magenta: '#E2007A',
                    green: '#56C02B',
                    teal: '#259793',
                },
            },
        },
    },

    plugins: [forms],
};
