var gulp = require('gulp');
var del = require('del');
var less = require('gulp-less');
var rename = require('gulp-rename');

// clean est synchrone, il doit être déclaré en 1er dans les task qui l'utilisent
gulp.task('clean', function() {
    del(['web/build/*']);
});

// Il semblerait que Clever Cloud ajoute une déclaration de sourcemap
// au bootstrap.min.css, du coup on met le fichier si non ca claque
gulp.task('sourcemap', function () {
    return gulp.src('node_modules/bootstrap/dist/css/bootstrap.css.map')
        .pipe(rename('bootstrap.min.css.map'))
        .pipe(gulp.dest('web/build/lib'));
});

gulp.task('copy-dependencies', function() {
    return gulp.src([
        'node_modules/bootstrap/dist/css/bootstrap.min.css',
        'node_modules/angular-xeditable-npm/dist/css/xeditable.css',
        'node_modules/jquery/dist/jquery.min.js',
        'node_modules/jquery.scrollto/jquery.scrollTo.min.js',
        'node_modules/angular/angular.js',
        'node_modules/angular-xeditable-npm/dist/js/xeditable.min.js'
    ]).pipe(gulp.dest('web/build/lib'));
});

gulp.task('less', function () {
    return gulp.src([
        'app/Resources/views/**/imports.less',
        'app/Resources/FOSUserBundle/views/bootstrap-forms.less'
    ]).pipe(less()).pipe(gulp.dest('web/build/css'));
});

gulp.task('js', function() {
    return gulp.src([
        'app/Resources/js/*.js'
    ]).pipe(gulp.dest('web/build/js'));
});

gulp.task('default', ['clean', 'sourcemap', 'copy-dependencies', 'less', 'js']);

gulp.task('watch', ['less', 'js'], function(){
    gulp.watch(['app/Resources/js/*.js', 'app/Resources/**/*.less'], ['less', 'js']);
});
