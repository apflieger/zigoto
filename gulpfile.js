var gulp = require('gulp');
var del = require('del');

gulp.task('clean', function(cb) {
    del(['web/build']);
    cb();
});

gulp.task('css', ['clean'], function() {
    return gulp.src([
        'node_modules/bootstrap/dist/css/bootstrap.min.css',
        'node_modules/angular/angular.js',
        'node_modules/angular-xeditable-npm/dist/css/xeditable.css'
    ]).pipe(gulp.dest('web/build'));
});

gulp.task('js', ['clean'], function() {
    return gulp.src([
        'app/Resources/js/*.js',
        'node_modules/angular-xeditable-npm/dist/js/xeditable.min.js'
    ]).pipe(gulp.dest('web/build'));
});

gulp.task('default', ['clean', 'css', 'js']);

gulp.task('watch', ['default'], function(){
    gulp.watch('app/Resources/js/*.js', ['js']);
});
