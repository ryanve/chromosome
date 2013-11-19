module.exports = function(grunt) {
    var path = require('path');

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        aok: { test: ['./test'] },
        jshint: {
            all: ['./'], // current and sub dirs
            sub: ['*/'], // sub dirs
            dir: ['*.js'], // current dir
            grunt: [path.basename(__filename)],
            options: {
                ignores: ['**/**/node_modules/', '**/**/vendor/', '**/**.min.js'],
                debug:true, expr:true, sub:true, boss:true, supernew:true, node:true, 
                undef:true, unused:true, devel:true, evil:true, laxcomma:true, eqnull:true, 
                browser:true, maxerr:10
            }
        }
    });

    grunt.loadNpmTasks('aok');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.registerTask('default', ['jshint:all']);
};