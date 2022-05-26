module.exports = function () {
    return {
        environment: process.env.ELEVENTY_ENV || 'development',
    }
}
