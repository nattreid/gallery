var gulp = require('gulp'),
    less = require('gulp-less'),
    minify = require('gulp-clean-css'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    streamqueue = require('streamqueue');


var paths = {
    'dev': {
        'less': './resources/assets/less/',
        'js': './resources/assets/js/',
        'vendor': './node_modules/'
    },
    'production': {
        'js': './assets/js',
        'css': './assets/css',
        'lang': './assets/js/i18n',
        'images': './assets/images/'
    }
};

// *****************************************************************************
// ************************************  JS  ***********************************

var bundledJS = [
    paths.dev.vendor + 'dropzone/dist/dropzone.js',
    paths.dev.vendor + 'jquery/dist/jquery.js',
    paths.dev.vendor + 'jquery-ui-sortable/jquery-ui.min.js',
    paths.dev.js + '*.js'
];

gulp.task('js', function () {
    return gulp.src(paths.dev.js + '*.js')
        .pipe(concat('gallery.js'))
        .pipe(gulp.dest(paths.production.js));
});

gulp.task('jsDropzone', function () {
    return gulp.src([
        paths.dev.vendor + 'dropzone/dist/dropzone.js',
        paths.dev.js + '*.js'
    ])
        .pipe(concat('gallery.dropzone.js'))
        .pipe(gulp.dest(paths.production.js));
});

gulp.task('jsBundled', function () {
    return gulp.src(bundledJS)
        .pipe(concat('gallery.bundled.js'))
        .pipe(gulp.dest(paths.production.js));
});

gulp.task('jsMin', function () {
    return gulp.src(paths.dev.js + '*.js')
        .pipe(concat('gallery.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest(paths.production.js));
});

gulp.task('jsDropzoneMin', function () {
    return gulp.src([
        paths.dev.vendor + 'dropzone/dist/dropzone.js',
        paths.dev.js + '*.js'
    ])
        .pipe(concat('gallery.dropzone.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest(paths.production.js));
});

gulp.task('jsBundledMin', function () {
    return gulp.src(bundledJS)
        .pipe(concat('gallery.bundled.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest(paths.production.js));
});

// *****************************************************************************
// ***********************************  CSS  ***********************************

var bundledCSS = [
    paths.dev.vendor + 'dropzone/dist/basic.css',
    paths.dev.vendor + 'dropzone/dist/dropzone.css',
];

gulp.task('css', function () {
    return gulp.src(paths.dev.less + '*.less')
        .pipe(less())
        .pipe(concat('gallery.css'))
        .pipe(gulp.dest(paths.production.css));
});

gulp.task('cssBundled', function () {
    return streamqueue({objectMode: true},
        gulp.src(bundledCSS),
        gulp.src(paths.dev.less + '*.less')
            .pipe(less())
    )
        .pipe(concat('gallery.bundled.css'))
        .pipe(gulp.dest(paths.production.css));
});

gulp.task('cssMin', function () {
    return gulp.src(paths.dev.less + '*.less')
        .pipe(less())
        .pipe(concat('gallery.min.css'))
        .pipe(minify({keepSpecialComments: 0}))
        .pipe(gulp.dest(paths.production.css));
});

gulp.task('cssBundledMin', function () {
    return streamqueue({objectMode: true},
        gulp.src(bundledCSS),
        gulp.src(paths.dev.less + '*.less')
            .pipe(less())
    )
        .pipe(concat('gallery.bundled.min.css'))
        .pipe(minify({keepSpecialComments: 0}))
        .pipe(gulp.dest(paths.production.css));
});

// *****************************************************************************

gulp.task('watch', function () {
    gulp.watch(paths.dev.js + '*.js', gulp.series('js', 'jsDropzone', 'jsBundled', 'jsMin', 'jsDropzoneMin', 'jsBundledMin'));
    gulp.watch(paths.dev.vendor + '*.js', gulp.series('js', 'jsDropzone', 'jsBundled', 'jsMin', 'jsDropzoneMin', 'jsBundledMin'));

    gulp.watch(paths.dev.less + '*.less', gulp.series('css', 'cssBundled', 'cssMin', 'cssBundledMin'));
    gulp.watch(paths.dev.vendor + '*.css', gulp.series('css', 'cssBundled', 'cssMin', 'cssBundledMin'));
});

gulp.task('default', gulp.series('js', 'jsDropzone', 'jsBundled', 'jsMin', 'jsDropzoneMin', 'jsBundledMin', 'css', 'cssBundled', 'cssMin', 'cssBundledMin', 'watch'));
