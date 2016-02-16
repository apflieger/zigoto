var gulp = require('gulp');
var del = require('del');
var less = require('gulp-less');

// clean est synchrone, il doit être déclaré en 1er dans les task qui l'utilisent
gulp.task('clean', function() {
    del(['web/build/*']);
});

gulp.task('copy-dependencies', function() {
    return gulp.src([
        'node_modules/bootstrap/dist/css/bootstrap.min.css',
        'node_modules/angular-xeditable-npm/dist/css/xeditable.css',
        'node_modules/angular/angular.js',
        'node_modules/angular-xeditable-npm/dist/js/xeditable.min.js'
    ]).pipe(gulp.dest('web/build'));
});

gulp.task('less', function () {
    return gulp.src('app/Resources/less/*.less')
        .pipe(less())
        .pipe(gulp.dest('web/build'));
});

gulp.task('js', function() {
    return gulp.src([
        'app/Resources/js/*.js'
    ]).pipe(gulp.dest('web/build'));
});

gulp.task('default', ['clean', 'copy-dependencies', 'less', 'js']);

gulp.task('watch', ['less', 'js'], function(){
    gulp.watch(['app/Resources/js/*.js', 'app/Resources/less/*.less'], ['less', 'js']);
});
