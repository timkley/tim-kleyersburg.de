const syntaxHighlight = require('@11ty/eleventy-plugin-syntaxhighlight')

module.exports = (eleventyConfig) => {
    eleventyConfig.setUseGitIgnore(false)

    // Watch and copy the temporary css file created by tailwindcss directly
    eleventyConfig.addWatchTarget('_tmp/bundle.css')
    eleventyConfig.addPassthroughCopy({'_tmp/bundle.css': 'bundle.css'})

    // Watch and copy our javascript source file to trigger a reload of the page
    eleventyConfig.addWatchTarget('src/js/bundle.js')
    eleventyConfig.addPassthroughCopy({'src/js/bundle.js': 'bundle.js'})

    // Copy images
    eleventyConfig.addPassthroughCopy({'src/img': 'img'})
    eleventyConfig.addPassthroughCopy('articles/**/*.{jpg,jpeg,png,gif}')

    eleventyConfig.addPlugin(syntaxHighlight)

    eleventyConfig.addShortcode('version', () => {
        return String(Date.now())
    })

    eleventyConfig.addShortcode('ogImage', function(url) {
        const imageService = process.env.ELEVENTY_PRODUCTION ? 'https://www.tim-kleyersburg.de/opengraph-image' : 'http://localhost:9999/.netlify/functions/screenshot';
        const openGraphImageUrl = process.env.ELEVENTY_PRODUCTION ? `https://www.tim-kleyersburg.de/opengraph/${url}` : `http://localhost:8080/opengraph/${url}`

        return `${imageService}/${encodeURIComponent(openGraphImageUrl)}`
    });

    eleventyConfig.addFilter('limit', (array, limit) => {
        return array.slice(0, limit)
    })

    eleventyConfig.addFilter('readtime', (content) => {
        const wpm = 250
        const wordCount = content.split(' ').length

        return Math.round(wordCount / wpm)
    })

    eleventyConfig.addFilter('formattedDate', (date) => {
        return date.toLocaleDateString('en-gb', { year: 'numeric', month: 'long', day: 'numeric'})
    })

    return {
        dir: {
            layouts: '_layouts'
        }
    }
}