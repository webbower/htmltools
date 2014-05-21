/* jshint globalstrict:true */
'use strict';

module.exports = function(grunt) {

    require('load-grunt-tasks')(grunt);

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        srcdir: 'Webbower',
        jshint: {
            options: {
                jshintrc: '.jshintrc',
                reporter: require('jshint-stylish')
            },
            gruntfile: {
                src: 'Gruntfile.js'
            }
        },
        watch: {
            gruntfile: {
                files: '<%= jshint.gruntfile.src %>',
                tasks: ['jshint:gruntfile']
            },
            phptest: {
                files: 'tests/**/*.php',
                tasks: ['phplint', 'phpcsfixer:find', 'phpunit']
            },
            phpsrc: {
                files: '<%= srcdir %>/*.php',
                tasks: ['phplint', 'phpcsfixer:find', 'phpunit']
            }
        },
        php: {
            dist: {
                options: {
                    port: 9000
                }
            }
        },
        phplint: {
            all: ['<%= srcdir %>/*.php']
        },
        phpunit: {
            unit: {
                dir: 'tests/'
            },
            options: {
                bin: 'vendor/bin/phpunit',
                bootstrap: 'vendor/autoload.php',
                colors: true
            }
        },
        phpcsfixer: {
            options: {
                level: 'all'
            },
            fix: {
                dir: '<%= srcdir %>'
            },
            find: {
                options: {
                    verbose: true,
                    dryRun: true,
                    diff: true
                },
                dir: '<%= srcdir %>'
            }
        }
    });

    grunt.registerTask('dev', 'Set up dev environment for PHP code', [
        'php:dist',
        'watch'
    ]);

    grunt.registerTask('serve', ['php']);
};