var gulp = require('gulp');

gulp.task('default', function() {
    return gulp.src('node_modules/bootstrap/dist/css/bootstrap.min.css').pipe(gulp.dest('web/build'));
});