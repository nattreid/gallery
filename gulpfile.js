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
    paths.dev.vendor + 'plupload/js/moxie.js',
    paths.dev.vendor + 'plupload/js/plupload.dev.js',
    paths.dev.vendor + 'plupload/js/jquery.ui.plupload/jquery.ui.plupload.js',
    paths.dev.js + '*.js'
];

var locale = {
    'cs': [
        paths.dev.vendor + 'plupload/js/i18n/cs.js'
    ]
};

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

gulp.task('jsCs', function () {
    return gulp.src(locale.cs)
        .pipe(concat('gallery.cs.js'))
        .pipe(gulp.dest(paths.production.lang));
});

gulp.task('jsCsMin', function () {
    return gulp.src(locale.cs)
        .pipe(concat('gallery.cs.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest(paths.production.lang));
});

// *****************************************************************************
// ***********************************  CSS  ***********************************

var boundledCSS = [
    paths.dev.vendor + 'plupload/js/jquery.ui.plupload/css/jquery.ui.plupload.css',
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

gulp.task('images', function () {
    return gulp.src(paths.dev.vendor + 'plupload/js/jquery.ui.plupload/img/**.*')
        .pipe(gulp.dest(paths.production.images));
});

gulp.task('watch', function () {
    gulp.watch(paths.dev.js + '*.js', ['js', 'jsBoundled', 'jsMin', 'jsBoundledMin', 'jsCs', 'jsCsMin']);
    gulp.watch(paths.dev.vendor + '*.js', ['js', 'jsBoundled', 'jsMin', 'jsBoundledMin', 'jsCs', 'jsCsMin']);

    gulp.watch(paths.dev.less + '*.less', ['css', 'cssBoundled', 'cssMin', 'cssBoundledMin']);
    gulp.watch(paths.dev.vendor + '*.css', ['css', 'cssBoundled', 'cssMin', 'cssBoundledMin']);
});

gulp.task('default', ['js', 'jsBoundled', 'jsMin', 'jsBoundledMin', 'jsCs', 'jsCsMin', 'css', 'cssBoundled', 'cssMin', 'cssBoundledMin', 'images', 'watch']);
