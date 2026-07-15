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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                "headline-xl": ["Inter"],
                "label-caps": ["Inter"],
                "headline-lg": ["Inter"],
                "label-sm": ["Inter"],
                "body-md": ["Inter"],
                "body-lg": ["Inter"]
            },
            colors: {
                brand: {
                    bg: '#F7F7F7',
                    accent: '#FFDD32',
                    primary: '#000000',
                    dark: '#000000',
                },
                "on-secondary-fixed-variant": "#6f3726",
                "surface-variant": "#353535",
                "surface-tint": "rgb(var(--accent-rgb))",
                "on-surface-variant": "rgba(var(--color-on-surface-variant), <alpha-value>)",
                "on-secondary-fixed": "#380d02",
                "secondary-fixed": "#ffdbd1",
                "primary-container": "rgb(var(--accent-rgb))",
                "error-container": "#93000a",
                "surface-container-low": "rgba(var(--color-surface-container-low), <alpha-value>)",
                "on-tertiary-container": "#00576b",
                "accent-glow": "rgba(var(--accent-rgb), 0.3)",
                "surface-container-highest": "rgba(var(--color-surface-container-highest), <alpha-value>)",
                "on-tertiary-fixed-variant": "#004e60",
                "surface-container": "rgba(var(--color-surface-container), <alpha-value>)",
                "error": "#ffb4ab",
                "primary-fixed-dim": "rgb(var(--accent-rgb))",
                "tertiary-container": "#19d2ff",
                "inverse-primary": "#815600",
                "secondary-fixed-dim": "#ffb59f",
                "primary": "rgba(var(--accent-rgb), <alpha-value>)",
                "on-primary-container": "var(--color-on-primary-container)",
                "glass-border": "var(--color-glass-border)",
                "on-tertiary-fixed": "#001f28",
                "on-primary": "#442b00",
                "outline-variant": "#514534",
                "secondary-container": "#723928",
                "secondary": "var(--color-secondary)",
                "tertiary": "#a7e7ff",
                "on-secondary-container": "#f4a58e",
                "outline": "#9f8e7a",
                "on-background": "rgba(var(--color-on-surface), <alpha-value>)",
                "surface": "rgba(var(--color-surface-container), <alpha-value>)",
                "surface-bright": "#393939",
                "tertiary-fixed": "#b6eaff",
                "inverse-on-surface": "#303030",
                "tertiary-fixed-dim": "#4ad6ff",
                "surface-dim": "rgba(var(--color-background), <alpha-value>)",
                "on-primary-fixed": "#291800",
                "surface-container-high": "rgba(var(--color-surface-container-high), <alpha-value>)",
                "surface-container-lowest": "#0e0e0e",
                "background": "rgba(var(--color-background), <alpha-value>)",
                "on-tertiary": "#003543",
                "on-surface": "rgba(var(--color-on-surface), <alpha-value>)",
                "on-secondary": "#532112",
                "inverse-surface": "rgba(var(--color-on-surface), <alpha-value>)",
                "on-error": "#690005",
                "on-primary-fixed-variant": "#624000",
                "surface-dark": "rgba(var(--color-surface-dark), <alpha-value>)",
                "on-error-container": "#ffdad6",
                "primary-fixed": "rgb(var(--accent-rgb))"
            },
            borderRadius: {
                "3xl": "1.5rem",
            },
            spacing: {
                "stack-lg": "2rem",
                "margin-desktop": "2.5rem",
                "stack-sm": "0.5rem",
                "margin-mobile": "1rem",
                "container-max": "1280px",
                "stack-md": "1rem",
                "gutter": "1.5rem"
            },
            fontSize: {
                "headline-xl": ["2.25rem", { "lineHeight": "2.75rem", "letterSpacing": "-0.02em" }],
                "headline-lg": ["1.625rem", { "lineHeight": "2.25rem", "letterSpacing": "-0.01em" }],
                "label-caps": ["0.75rem", { "lineHeight": "1rem", "letterSpacing": "0.1em" }],
                "label-sm": ["0.875rem", { "lineHeight": "1.25rem" }],
                "body-md": ["0.9375rem", { "lineHeight": "1.5rem" }],
                "body-lg": ["1.0625rem", { "lineHeight": "1.625rem" }]
            }
        },
    },

    plugins: [forms],
};
