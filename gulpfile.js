const gulp = require('gulp'),
    twig = require('gulp-twig'),
    source = require('vinyl-source-stream'),
    browserify = require('browserify'),
    browserSync = require('browser-sync').create(),
    uglify = require('gulp-uglify'),
    sequence = require('run-sequence');

gulp.task('default', function (done) {
    sequence('twig', 'css', 'purge-css', 'js', 'uglify')
});

gulp.task('dev', function () {
    browserSync.init({
        proxy: "127.0.0.1:8000",
    });
    gulp.watch('src/css/**/*.css', ['css']);
    gulp.watch('src/js/**/*.{js,vue}', ['js']);
    gulp.watch('tailwind.config.js', ['css']);
    gulp.watch('src/twig/**/*.twig', ['twig']);
});

gulp.task('js', function () {
    var b = browserify({
        entries: 'src/js/bundle.js',
        debug: true,
        transform: [
            'babelify',
            'vueify'
        ]
    });

    return b.bundle()
        .pipe(source('bundle.js'))
        .pipe(gulp.dest('./public/js'))
        .pipe(browserSync.stream());
});

gulp.task('uglify', function () {
    return gulp.src('public/js/bundle.js')
        .pipe(uglify())
        .pipe(gulp.dest('./public/js'));
});

gulp.task('css', function () {
    const postcss = require('gulp-postcss');

    return gulp.src('src/css/bundle.css')
        .pipe(postcss([
            require('postcss-import'),
            require('postcss-nested'),
            require('tailwindcss'),
            require('autoprefixer')
        ]))
        .pipe(gulp.dest('./public/css'))
        .pipe(browserSync.stream());
});

gulp.task('purge-css', function() {
    const purgecss = require('@fullhuman/postcss-purgecss')({
        content: [
            './public/*.html',
            './public/*.js'
        ],
        defaultExtractor: content => content.match(/[A-Za-z0-9-_:/]+/g) || []
    });

    const postcss = require('gulp-postcss');

    return gulp.src('public/css/bundle.css')
        .pipe(postcss([
            purgecss
        ]))
        .pipe(gulp.dest('./public/css'));
});

gulp.task('twig', function () {
    return gulp.src('src/twig/!(_)*.twig')
        .pipe(twig())
        .pipe(gulp.dest('./public'))
        .pipe(browserSync.stream());
});
