module.exports = (eleventyConfig) => {
    eleventyConfig.setUseGitIgnore(false)

    eleventyConfig.addWatchTarget('_tmp/bundle.css')
    eleventyConfig.addWatchTarget('src/js/bundle.js')

    eleventyConfig.addPassthroughCopy({'_tmp/bundle.css': 'bundle.css'})
    eleventyConfig.addPassthroughCopy({'src/js/bundle.js': 'bundle.js'})
    eleventyConfig.addPassthroughCopy({'src/img': 'img'})

    eleventyConfig.addShortcode('version', function () {
        return String(Date.now())
    })
}