module.exports = function(grunt) {

    grunt.initConfig({
        buildDir: "web/build",
        clean: {
            build: "<%= buildDir %>"
        },
        copy: {
            external_dependencies: {
                expand: true,
                src:[
                    'node_modules/bootstrap/dist/css/bootstrap.min.css',
                    'node_modules/angular-xeditable-npm/dist/css/xeditable.css',
                    'node_modules/jquery/dist/jquery.min.js',
                    'node_modules/jquery.scrollto/jquery.scrollTo.min.js',
                    'node_modules/angular/angular.js',
                    'node_modules/angular-xeditable-npm/dist/js/xeditable.min.js'
                ],
                flatten: true,
                dest: "<%= buildDir %>/lib/"
            },
            // Il semblerait que Clever Cloud ajoute une déclaration de sourcemap
            // au bootstrap.min.css, du coup on met le fichier si non ca claque
            bootstrap_sourcemap: {
                src: ["node_modules/bootstrap/dist/css/bootstrap.css.map"],
                dest: "<%= buildDir %>/lib/bootstrap.min.css.map"
            },
            js: {
                expand: true,
                cwd: "app/Resources/js",
                src: ["**/*.js"],
                dest: "<%= buildDir %>/js/",
            }
        },
        less: {
            csstree: {
                expand: true,
                cwd: "app/Resources/views",
                src: ["**/imports.less"],
                dest: "<%= buildDir %>/css/",
                ext: ".css"
            },
            fosuserbundle: {
                src: ["app/Resources/FOSUserBundle/views/bootstrap-forms.less"],
                dest: "<%= buildDir %>/css/bootstrap-forms.css"
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-less');

    grunt.registerTask('default', ['clean', 'copy', 'less']);

};