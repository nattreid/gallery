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
        'vendor': './resources/assets/vendor/'
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

var boundledJS = [
    paths.dev.vendor + 'dropzone/dist/dropzone.js',
    paths.dev.js + '*.js'
];

gulp.task('js', function () {
    return gulp.src(paths.dev.js + '*.js')
        .pipe(concat('gallery.js'))
        .pipe(gulp.dest(paths.production.js));
});

gulp.task('jsBoundled', function () {
    return gulp.src(boundledJS)
        .pipe(concat('gallery.boundled.js'))
        .pipe(gulp.dest(paths.production.js));
});

gulp.task('jsMin', function () {
    return gulp.src(paths.dev.js + '*.js')
        .pipe(concat('gallery.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest(paths.production.js));
});

gulp.task('jsBoundledMin', function () {
    return gulp.src(boundledJS)
        .pipe(concat('gallery.boundled.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest(paths.production.js));
});

// *****************************************************************************
// ***********************************  CSS  ***********************************

var boundledCSS = [
    paths.dev.vendor + 'dropzone/dist/dropzone.basic.css',
    paths.dev.vendor + 'dropzone/dist/dropzone.dropzone.css',
];

gulp.task('css', function () {
    return gulp.src(paths.dev.less + '*.less')
        .pipe(less())
        .pipe(concat('gallery.css'))
        .pipe(gulp.dest(paths.production.css));
});

gulp.task('cssBoundled', function () {
    return streamqueue({objectMode: true},
        gulp.src(boundledCSS),
        gulp.src(paths.dev.less + '*.less')
            .pipe(less())
    )
        .pipe(concat('gallery.boundled.css'))
        .pipe(gulp.dest(paths.production.css));
});

gulp.task('cssMin', function () {
    return gulp.src(paths.dev.less + '*.less')
        .pipe(less())
        .pipe(concat('gallery.min.css'))
        .pipe(minify({keepSpecialComments: 0}))
        .pipe(gulp.dest(paths.production.css));
});

gulp.task('cssBoundledMin', function () {
    return streamqueue({objectMode: true},
        gulp.src(boundledCSS),
        gulp.src(paths.dev.less + '*.less')
            .pipe(less())
    )
        .pipe(concat('gallery.boundled.min.css'))
        .pipe(minify({keepSpecialComments: 0}))
        .pipe(gulp.dest(paths.production.css));
});

// *****************************************************************************

gulp.task('watch', function () {
    gulp.watch(paths.dev.js + '*.js', ['js', 'jsBoundled', 'jsMin', 'jsBoundledMin']);
    gulp.watch(paths.dev.vendor + '*.js', ['js', 'jsBoundled', 'jsMin', 'jsBoundledMin']);

    gulp.watch(paths.dev.less + '*.less', ['css', 'cssBoundled', 'cssMin', 'cssBoundledMin']);
    gulp.watch(paths.dev.vendor + '*.css', ['css', 'cssBoundled', 'cssMin', 'cssBoundledMin']);
});

gulp.task('default', ['js', 'jsBoundled', 'jsMin', 'jsBoundledMin', 'css', 'cssBoundled', 'cssMin', 'cssBoundledMin', 'watch']);
