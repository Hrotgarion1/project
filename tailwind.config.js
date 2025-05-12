import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'primary-0': '#28618A',
                'primary-1': '#6795B5',
                'primary-2': '#407499',
                'primary-3': '#164C73',
                'primary-4': '#07385A',
                'main-0': '#2E4053',
                'main-1': '#3498DB',
                'secondary-0': '#8BC34A',
                'secondary-1': '#3E8E41',
                'secondary-2': '#FFC107',
                'secondary-3': '#FFA07A',
                'neutral-0': '#FFFFFF',
                'neutral-1': '#333333',
                'neutral-2': '#444444',
                'neutral-3': '#F7F7F7',
                'neutral-4': '#E5E5E5',
            },
            width: {
                '120px': '120px',
                '240px': '240px',
                '480px': '480px',
                '640px': '640px',
                '800px': '800px',
            },
            minWidth: {
                '80px': '80px',
            },
            maxWidth: {
                '100px': '100px',
                '240px': '240px',
                '480px': '480px',
                '640px': '640px',
                '800px': '800px',
            },
            borderRadius: {
                '2xl': '3rem', // 48px
            },
            screens: {
                'sm': '640px',
                'md': '768px',
                'lg': '1024px',
                'xl': '1280px',
            },
        },
    },

    plugins: [forms, typography],
};
