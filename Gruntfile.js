module.exports = function(grunt) {

    grunt.initConfig({
        sourceDir: "app/Resources",
        treeDir: "<%= sourceDir %>/views",
        buildDir: "web/build",
        clean: {
            build: "<%= buildDir %>",
            templateTree: "<%= treeDir %>/**/*.gen.*"
        },
        copy: {
            external_dependencies: {
                expand: true,
                src:[
                    'node_modules/normalize.css/normalize.css',
                    'node_modules/angular-xeditable-npm/dist/css/xeditable.css',
                    'node_modules/jquery/dist/jquery.min.js',
                    'node_modules/jquery.scrollto/jquery.scrollTo.min.js',
                    'node_modules/angular/angular.js',
                    'node_modules/angular-xeditable-npm/dist/js/xeditable.min.js'
                ],
                flatten: true,
                dest: "<%= buildDir %>/lib/"
            },
            js: {
                expand: true,
                cwd: "<%= sourceDir %>/js",
                src: ["**/*.js"],
                dest: "<%= buildDir %>/js/",
            },
            images: {
                expand: true,
                cwd: "<%= sourceDir %>/views",
                src: ["**/*.png", "**/*.jpg", "**/*.jpeg"],
                dest: "<%= buildDir %>/css/"
            }
        },
        csstree: {
            views: {
                options: {
                    ext: '.less'
                },
                src: '<%= treeDir %>'
            }
        },
        less: {
            csstree: {
                expand: true,
                cwd: "<%= treeDir %>",
                src: ["**/branch.gen.less"],
                dest: "<%= buildDir %>/css/",
                ext: ".css",
                extDot: 'last'
            },
            fosuserbundle: {
                src: ["app/Resources/FOSUserBundle/views/fos-user-bundle.less"],
                dest: "<%= buildDir %>/css/fos-user-bundle.css"
            }
        },
        watch: {
            options: {
                livereload: true
            },
            less: {
                files: ['app/Resources/views/**/*.less'],
                tasks: ['less']
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-csstree');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', ['clean', 'copy', 'csstree', 'less']);

};