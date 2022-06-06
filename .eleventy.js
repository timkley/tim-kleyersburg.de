module.exports = (eleventyConfig) => {
    eleventyConfig.setUseGitIgnore(false)

    // Watch and copy our source files to trigger a reload of the page
    eleventyConfig.addWatchTarget('src/js/bundle.js')
    eleventyConfig.addWatchTarget('src/css/bundle.css')
    eleventyConfig.addWatchTarget('tailwind.config.js')

    // Copy images
    eleventyConfig.addPassthroughCopy({ 'src/img': 'img' })
    // Copy article media
    eleventyConfig.addPassthroughCopy(
        'src/content/articles/**/*.{jpg,jpeg,png,gif,mp4}'
    )
    // Copy fonts
    eleventyConfig.addPassthroughCopy({ 'src/css/fonts': 'fonts' })
    // Copy favicon
    eleventyConfig.addPassthroughCopy({ 'src/favicon': '/' })

    // local plugins
    eleventyConfig.addPlugin(require('./_eleventyjs/shortcodes'))
    eleventyConfig.addPlugin(require('./_eleventyjs/filters'))

    // external plugins
    eleventyConfig.addPlugin(require('eleventy-plugin-torchlight'))
    eleventyConfig.addPlugin(require('@11ty/eleventy-plugin-rss'))

    return {
        markdownTemplateEngine: 'njk',
        dir: {
            input: 'src/content',
            layouts: '_layouts',
        },
    }
}
