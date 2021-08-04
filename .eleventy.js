module.exports = (eleventyConfig) => {
    eleventyConfig.setUseGitIgnore(false)

    // Watch and copy the temporary css file created by tailwindcss directly
    eleventyConfig.addWatchTarget('_tmp/bundle.css')
    eleventyConfig.addPassthroughCopy({'_tmp/bundle.css': 'bundle.css'})

    // Watch and copy our javascript source file to trigger a reload of the page
    eleventyConfig.addWatchTarget('src/js/bundle.js')
    eleventyConfig.addPassthroughCopy({'src/js/bundle.js': 'bundle.js'})

    // Copy all images
    eleventyConfig.addPassthroughCopy({'src/img': 'img'})

    eleventyConfig.addShortcode('version', function () {
        return String(Date.now())
    })

    eleventyConfig.addFilter('limit', function (array, limit) {
        return array.slice(0, limit)
    })

    return {
        dir: {
            layouts: '_layouts'
        }
    }
}