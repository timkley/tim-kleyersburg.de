const util = require('util')

module.exports = (eleventyConfig) => {
    eleventyConfig.setUseGitIgnore(false)

    // Watch and copy our source files to trigger a reload of the page
    eleventyConfig.addWatchTarget('src/js/bundle.js')
    eleventyConfig.addWatchTarget('src/css/bundle.css')
    eleventyConfig.addWatchTarget('tailwind.config.js')

    // Copy images
    eleventyConfig.addPassthroughCopy({'src/img': 'img'})
    eleventyConfig.addPassthroughCopy('src/content/articles/**/*.{jpg,jpeg,png,gif,mp4}')

    // Copy fonts
    eleventyConfig.addPassthroughCopy({'src/css/fonts': 'fonts'})
    eleventyConfig.addPlugin(require('eleventy-plugin-torchlight'))
    eleventyConfig.addPlugin(require('@11ty/eleventy-plugin-rss'))

    eleventyConfig.addShortcode('version', () => {
        return String(Date.now())
    })

    eleventyConfig.addShortcode('ogImage', function (url) {
        const imageService = process.env.ELEVENTY_ENV === 'production' ? 'https://www.tim-kleyersburg.de/screenshot' : 'http://localhost:9999/.netlify/functions/screenshot';
        const openGraphImageUrl = process.env.ELEVENTY_ENV === 'production' ? `https://www.tim-kleyersburg.de/opengraph/${url}` : `http://localhost:8080/opengraph/${url}`

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
        return date.toLocaleDateString('en-gb', {year: 'numeric', month: 'long', day: 'numeric'})
    })

    eleventyConfig.addFilter('console', (value) => {
        const str = util.inspect(value);
        return `<div style="font-family: monospace; white-space: pre-wrap;">${unescape(str)}</div>`
    })

    return {
        markdownTemplateEngine: 'njk',
        dir: {
            input: 'src/content',
            layouts: '_layouts'
        }
    }
}