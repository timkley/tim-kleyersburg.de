module.exports = {
    mode: 'jit',
    important: true,
    purge: [
        '*.njk',
        '*.json',
        '_includes/**/*.njk',
        '_layouts/**/*.njk',
        'articles/**/*.njk',
    ],
    theme: {
        extend: {
            typography: {
                DEFAULT: {
                    css: {
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
                            fontStyle: 'initial',
                        },
                        'code::before': {
                            content: '',
                        },
                        'code::after': {
                            content: '',
                        },
                    }
                }
            }
        },
    },
    plugins: [
        require('@tailwindcss/typography')
    ],
}
