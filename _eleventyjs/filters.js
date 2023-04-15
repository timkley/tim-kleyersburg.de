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

  // Kindly borrowed and adapted from https://github.com/11ty/eleventy/discussions/2534#discussioncomment-3415878
  eleventyConfig.addNunjucksFilter('related', function (collection = [], limit = 4) {
    const { tags: requiredTags, page } = this.ctx
    return collection.filter(post => {
      return post.url !== page.url && post.data.tags?.some(tag => tag !== 'article' && requiredTags.includes(tag))
    })
      .slice(0, limit)
  })
}
