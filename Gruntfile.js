const sass = require('node-sass');
module.exports = function(grunt) {
    grunt.initConfig({
        sass: {
            dist: {
                options: {
                    implementation: sass,
                    sourcemap: false,
                    compress: false,
                    yuicompress: false,
                    style: 'expanded',
                },
                files: {
                    'styles.css' : 'scss/styles.scss'
                }
            },
        }
    });
    grunt.loadNpmTasks('grunt-sass');
    grunt.registerTask('default',['sass']);
}