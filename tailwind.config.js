import colors from 'tailwindcss/colors';

/** @type {import('tailwindcss').Config} */
export default {
    theme: {
        extend: {
            colors: {
                zinc: colors.gray,
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
}
