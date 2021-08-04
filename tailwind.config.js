module.exports = {
    mode: 'jit',
    purge: [
        '*.njk',
        '*.json',
        '_includes/**/*.njk',
        '_layouts/**/*.njk'
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
                        }
                    }
                }
            }
        },
    },
    plugins: [
        require('@tailwindcss/typography')
    ],
}
