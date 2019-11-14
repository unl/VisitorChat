/*global module:false*/
module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        // Task configuration.
        svgmin: {
            options: {
                plugins: [
                    { removeViewBox: false },
                    { removeUselessStrokeAndFill: false }
                ]
            },
            dist: {
                files: [{
                  expand: true,
                  cwd: 'images/svg/originals',
                  src: ['*.svg'],
                  dest: 'images/svg/optimized'
                }]
            }
        },
        lesshint: {
            options: {
                lesshintrc: true
            },
            files: {
                src: ['less/*.less']
            }
        },
        less: {
            client: {
                options: {
                    paths: ['./less'],
                    plugins: [
                        new (require('less-plugin-autoprefix'))({browsers: [
                          "last 1 Chrome version",
                          "last 1 Explorer version",
                          "last 1 Firefox version",
                          "Firefox ESR",
                          "last 1 Safari version",
                          "Android >= 4.0",
                          "BlackBerry >= 4.0",
                          "iOS >= 10",
                        ]}),
                        new (require('less-plugin-clean-css'))
                    ]
                },
                files: {
                    'css/VisitorChat/5.0/client.css': 'less/client.less'
                }
            },
            operator: {
                options: {
                    paths: ['./less'],
                    plugins: [
                        new (require('less-plugin-autoprefix'))({browsers: [
                          "last 1 Chrome version",
                          "last 1 Explorer version",
                          "last 1 Firefox version",
                          "Firefox ESR",
                          "last 1 Safari version",
                          "Android >= 4.0",
                          "BlackBerry >= 4.0",
                          "iOS >= 10",
                        ]}),
                        new (require('less-plugin-clean-css'))
                    ]
                },
                files: {
                    'css/VisitorChat/5.0/operator.css': 'less/operator.less'
                }
            }
        },
        watch: {
            svgmin: {
                files: ['images/svg/originals/*'],
                tasks: ['svgmin']
            },
            less: {
                files: ['less/*', 'less/*/*'],
                tasks: ['lesshint', 'less']
            }
        }
    });

    // These plugins provide necessary tasks.
    require('load-grunt-tasks')(grunt);

    // Default task.
    grunt.registerTask('default', ['watch']);
};
