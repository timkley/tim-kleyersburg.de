module.exports = (eleventyConfig, options) => {
	eleventyConfig.addFilter('limit', (array, limit) => {
		return array.slice(0, limit)
	})

	eleventyConfig.addFilter('readtime', (content) => {
		const wpm = 250
		const wordCount = content.split(' ').length

		return Math.ceil(wordCount / wpm)
	})

	eleventyConfig.addFilter('formattedDate', (date) => {
		return date.toLocaleDateString('en-gb', {
			year: 'numeric',
			month: 'long',
			day: 'numeric',
		})
	})
}