var gulp = require('gulp');

gulp.task('external', function() {
    return gulp.src([
        'node_modules/bootstrap/dist/css/bootstrap.min.css',
        'node_modules/angular/angular.js'
    ]).pipe(gulp.dest('web/build'));
});

gulp.task('js', function() {
    return gulp.src([
        'app/Resources/js/*.js'
    ]).pipe(gulp.dest('web/build'));
});

gulp.task('default', ['external', 'js']);

gulp.task('watch', function(){
    gulp.watch('app/Resources/js/*.js', ['js']);
});
