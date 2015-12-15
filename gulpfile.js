var gulp = require('gulp');

gulp.task('default', function() {
    return gulp.src([
        'node_modules/bootstrap/dist/css/bootstrap.min.css',
        'node_modules/angular/angular.js'
    ]).pipe(gulp.dest('web/build'));
});