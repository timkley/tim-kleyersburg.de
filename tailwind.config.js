module.exports = {
	mode: 'jit',
	content: ['src/content/**/*.{json,njk,md}'],
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
			}),
		},
	},
	plugins: [
		require('@tailwindcss/line-clamp'),
		require('@tailwindcss/typography'),
	],
	corePlugins: {
		textOpacity: false,
		backgroundOpacity: false,
		borderOpacity: false,
		divideOpacity: false,
		placeholderOpacity: false,
		ringOpacity: false,
	},
}
