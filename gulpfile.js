// Load plugins
var gulp = require('gulp'),
    sass = require('gulp-ruby-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    minifycss = require('gulp-minify-css'),
    jshint = require('gulp-jshint'),
    uglify = require('gulp-uglify'),
    imagemin = require('gulp-imagemin'),
    rename = require('gulp-rename'),
    clean = require('gulp-clean'),
    concat = require('gulp-concat'),
    notify = require('gulp-notify'),
    cache = require('gulp-cache'),
    livereload = require('gulp-livereload'),
    lr = require('tiny-lr'),
    server = lr();
// Styles
gulp.task('styles', function() {
    return gulp.src('public/resources/*.scss').pipe(sass({
        style: 'expanded',
    })).pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4')).pipe(gulp.dest('dist/styles')).pipe(rename({
        suffix: '.min'
    })).pipe(minifycss()).pipe(livereload(server)).pipe(gulp.dest('dist/styles')).pipe(notify({
        message: 'Styles task complete'
    }));
});
// Scripts
gulp.task('scripts', function() {
    return gulp.src('public/resources/*.js').pipe(jshint('.jshintrc')).pipe(jshint.reporter('default')).pipe(concat('main.js')).pipe(gulp.dest('dist/scripts')).pipe(rename({
        suffix: '.min'
    })).pipe(uglify()).pipe(livereload(server)).pipe(gulp.dest('dist/scripts')).pipe(notify({
        message: 'Scripts task complete'
    }));
});
// Images
gulp.task('images', function() {
    return gulp.src('public/resources/*').pipe(cache(imagemin({
        optimizationLevel: 3,
        progressive: true,
        interlaced: true
    }))).pipe(livereload(server)).pipe(gulp.dest('dist/images')).pipe(notify({
        message: 'Images task complete'
    }));
});
// Clean
gulp.task('clean', function() {
    return gulp.src(['dist/styles', 'dist/scripts', 'dist/images'], {
        read: false
    }).pipe(clean());
});
// Default task
gulp.task('default', ['clean'], function() {
    gulp.run('styles', 'scripts', 'images');
});
// Watch
gulp.task('watch', function() {
    // Listen on port 35729
    server.listen(35729, function(err) {
        if (err) {
            return console.log(err)
        };
        // Watch .scss files
        gulp.watch('public/resources/*.scss', function(event) {
            console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
            gulp.run('styles');
        });
        // Watch .js files
        gulp.watch('public/resources/*.js', function(event) {
            console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
            gulp.run('scripts');
        });
        // Watch image files
        gulp.watch('public/resources/*', function(event) {
            console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
            gulp.run('images');
        });
    });
});