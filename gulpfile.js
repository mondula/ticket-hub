const gulp = require('gulp');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const cleanCSS = require('gulp-clean-css');
const rename = require('gulp-rename');

// Admin JS task
gulp.task('admin-js', function() {
    return gulp.src('ticket-hub/js/admin/**/*.js')
        .pipe(concat('ticket-hub-admin.js'))
        .pipe(gulp.dest('ticket-hub/dist/js'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('ticket-hub/dist/js'));
});

// Non-admin JS task
gulp.task('public-js', function() {
    return gulp.src('ticket-hub/js/public/**/*.js')
        .pipe(concat('ticket-hub.js'))
        .pipe(gulp.dest('ticket-hub/dist/js'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('ticket-hub/dist/js'));
});

// Admin CSS task
gulp.task('admin-css', function() {
    return gulp.src('ticket-hub/css/admin/**/*.css')
    .pipe(concat('ticket-hub-admin.css'))
    .pipe(gulp.dest('ticket-hub/dist/css'))
    .pipe(cleanCSS())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest('ticket-hub/dist/css'));
});

// Non-admin CSS task
gulp.task('public-css', function() {
    return gulp.src('ticket-hub/css/public/**/*.css')
    .pipe(concat('ticket-hub.css'))
    .pipe(gulp.dest('ticket-hub/dist/css'))
    .pipe(cleanCSS())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest('ticket-hub/dist/css'));
});

// Default task
gulp.task('default', gulp.parallel('admin-js', 'public-js', 'admin-css', 'public-css'));