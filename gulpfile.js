var gulp = require('gulp'),
        less = require('gulp-less'),
        minify = require('gulp-clean-css'),
        uglify = require('gulp-uglify'),
        rename = require('gulp-rename');

var path = './assets/';

gulp.task('js', function () {
    return gulp.src(path + 'gallery.js')
            .pipe(rename({suffix: '.min'}))
            .pipe(uglify())
            .pipe(gulp.dest(path));
});

gulp.task('less', function () {
    return gulp.src(path + 'gallery.less')
            .pipe(rename({suffix: '.min'}))
            .pipe(less())
            .pipe(minify({keepSpecialComments: 0}))
            .pipe(gulp.dest(path));
});

gulp.task('watch', function () {
    gulp.watch(path + 'gallery.js', ['js']);
    gulp.watch(path + 'gallery.less', ['less']);
});

gulp.task('default', ['js', 'less', 'watch']); 