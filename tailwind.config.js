import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                primary: 'rgb(var(--color-primary) / <alpha-value>)',
                accent: 'rgb(var(--color-accent) / <alpha-value>)',
            },
            fontFamily: {
                sans: ['var(--font-body)', ...defaultTheme.fontFamily.sans],
                heading: ['var(--font-heading)', ...defaultTheme.fontFamily.sans],
                body: ['var(--font-body)', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [forms, typography],
};
