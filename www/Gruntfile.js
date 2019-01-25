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
        'curl-dir': {
            mixins: {
                src: [
                'https://raw.githubusercontent.com/unl/wdntemplates/4.1/wdn/templates_4.1/less/_mixins/breakpoints.less',
                'https://raw.githubusercontent.com/unl/wdntemplates/4.1/wdn/templates_4.1/less/_mixins/colors.less',
                'https://raw.githubusercontent.com/unl/wdntemplates/4.1/wdn/templates_4.1/less/_mixins/fonts.less',
                'https://raw.githubusercontent.com/unl/wdntemplates/4.1/wdn/templates_4.1/less/_mixins/functions.less',
                'https://raw.githubusercontent.com/unl/wdntemplates/4.1/wdn/templates_4.1/less/_mixins/vars.less'
                ],
                dest: 'less/mixins'
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
                        new (require('less-plugin-autoprefix'))({browsers: ["last 2 versions"]})
                    ]
                },
                files: {
                    'css/VisitorChat/4.0/client.css': 'less/client.less'
                }
            },
            operator: {
                options: {
                    paths: ['./less'],
                    plugins: [
                        new (require('less-plugin-autoprefix'))({browsers: ["last 2 versions"]})
                    ]
                },
                files: {
                    'css/VisitorChat/4.0/operator.css': 'less/operator.less'
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
                tasks: ['curl-dir', 'lesshint', 'less']
            }
        }
    });

    // These plugins provide necessary tasks.
    require('load-grunt-tasks')(grunt);

    // Default task.
    grunt.registerTask('default', ['watch']);
};
