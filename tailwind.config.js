module.exports = {
    theme: {
        extend: {
            colors: {
                'blue-200-90': 'rgba(190, 227, 248, .90)',
                'red-200-90': 'rgba(254, 215, 215, .90)'
            }
        },
        container: {
            center: true
        }
    },
    variants: {
        backgroundColor: ['responsive', 'hover', 'group-hover']
    },
    plugins: [],
}