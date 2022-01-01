module.exports = {
    mode: 'jit',
    content: [
        '*.njk',
        '*.json',
        '_includes/**/*.njk',
        '_layouts/**/*.njk',
        'articles/**/*.njk',
        'articles/**/*.md'
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Source Sans Pro', 'sans-serif'],
            },
            typography: (theme) => ({
                DEFAULT: {
                    css: {
                        maxWidth: '72ch',
                        a: {
                            fontWeight: 400,
                            textDecoration: 'none',
                            wordBreak: 'break-word'
                        },
                        blockquote: {
                            fontWeight: 400,
                            fontStyle: 'normal',
                            quotes: 'none'
                        },
                        code: {
                            border: `1px solid ${theme('colors.gray.300')}`,
                            borderRadius: theme('borderRadius.sm'),
                            fontStyle: 'initial',
                            padding: `2px ${theme('padding.1')}`
                        },
                        'code::before': {
                            content: '',
                        },
                        'code::after': {
                            content: '',
                        },
                        img: {
                            borderRadius: theme('borderRadius.lg'),
                            boxShadow: theme('boxShadow.md')
                        }
                    }
                }
            })
        },
    },
    plugins: [
        require('@tailwindcss/typography')
    ],
}
