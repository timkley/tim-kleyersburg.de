import colors from 'tailwindcss/colors'
import typography from '@tailwindcss/typography'

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/livewire/flux-pro/stubs/**/*.blade.php',
        './vendor/livewire/flux/stubs/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                gray: colors.slate,
            },
            fontFamily: {
                sans: ['Source Sans Pro', 'sans-serif'],
                ibm: ['IBM Plex Sans', 'sans-serif'],
            },
            typography: (theme) => ({
                DEFAULT: {
                    css: {
                        maxWidth: '72ch',
                        a: {
                            fontWeight: 400,
                            textDecoration: 'none',
                            wordBreak: 'break-word',
                        },
                        blockquote: {
                            fontWeight: 400,
                            fontStyle: 'normal',
                            quotes: 'none',
                        },
                        code: {
                            backgroundColor: theme('colors.sky.200'),
                            borderRadius: theme('borderRadius.sm'),
                            fontStyle: 'initial',
                            padding: `2px ${theme('padding.1')}`,
                        },
                        'code::before': {
                            content: '',
                        },
                        'code::after': {
                            content: '',
                        },
                        img: {
                            borderRadius: theme('borderRadius.lg'),
                            boxShadow: theme('boxShadow.md'),
                        },
                    },
                },
                invert: {
                    css: {
                        'pre code': {
                            backgroundColor: 'transparent',
                        },
                        code: {
                            backgroundColor: theme('colors.sky.900'),
                        },
                    },
                },
            })
        },
    },
    plugins: [typography],
}
