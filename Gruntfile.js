module.exports = function(grunt) {

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    concat: {
      options: {
        separator: ';'
      },
      dist: {
        src: ['js/jquery*', 'js/*.js'],
        dest: 'dist/<%= pkg.name %>.js'
      }
    },
    uglify: {
      options: {
        banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
      },
      dist: {
        files: {
          'dist/<%= pkg.name %>.min.js': ['<%= concat.dist.dest %>']
        }
      }
    },
    jshint: {
      files: ['Gruntfile.js', 'js/*.js'],
      options: {
        // options here to override JSHint defaults
        globals: {
          jQuery: true,
          console: true,
          module: true,
          document: true
        }
      }
    },
    tags: {
      js : {
            options: {
                scriptTemplate: '<script type="text/javascript" src="{{ path }}" language="javascript"></script>',
                openTag: '<!-- start js template tags -->',
                closeTag: '<!-- end js template tags -->',
                absolutePath: true
            },
            src: [
                'js/*.js',
            ],
            dest: 'application/views/partial/header.php'
       },
       minjs : {
           options: {
               scriptTemplate: '<script type="text/javascript" src="{{ path }}" language="javascript"></script>',
               openTag: '<!-- start minjs template tags -->',
               closeTag: '<!-- end minjs template tags -->',
               absolutePath: true
           },
           src: [
               'dist/*min.js',
           ],
           dest: 'application/views/partial/header.php'
      }
    },
    watch: {
      files: ['<%= jshint.files %>'],
      tasks: ['jshint']
    }
  });

  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-script-link-tags');

  grunt.registerTask('default', ['tags:js', 'tags:css', 'concat', 'uglify', 'tags:minjs']);

};
