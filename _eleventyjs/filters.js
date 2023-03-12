module.exports = (eleventyConfig, options) => {
	eleventyConfig.addFilter('limit', (array, limit) => {
		return array.slice(0, limit)
	})

	eleventyConfig.addFilter('readtime', (content) => {
		const wpm = 250
		const wordCount = content.split(' ').length
		const minutes = Math.ceil(wordCount / wpm)

		return minutes + (minutes === 1 ? ' minute' : ' minutes')
	})

	eleventyConfig.addFilter('formattedDate', (date) => {
		return date.toLocaleDateString('en-gb', {
			year: 'numeric',
			month: 'long',
			day: 'numeric',
		})
	})
}
