const path = require('path')
const Image = require('@11ty/eleventy-img')

async function imageShortcode(
	src,
	alt,
	sizes = '(min-width: 600px) 600w, 375w'
) {
	src = this.page?.inputPath
		? `${path.dirname(this.page.inputPath)}/${src}`
		: src

	let metadata = await Image(src, {
		widths: [375, 650],
		formats: ['avif', 'webp', 'jpeg'],
		outputDir: './_site/img/',
	})

	let imageAttributes = {
		alt,
		sizes,
		loading: 'lazy',
		decoding: 'async',
	}

	return Image.generateHTML(metadata, imageAttributes, {
		whitespaceMode: 'inline',
	})
}

module.exports = (eleventyConfig, options) => {
	eleventyConfig.addShortcode('version', () => {
		return String(Date.now())
	})

	eleventyConfig.addNunjucksAsyncShortcode('image', imageShortcode)
}
