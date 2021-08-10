module.exports = function (grunt) {
  grunt.initConfig({
    clean: {
      composer: ["vendor"],
      licenses: ["public/license/"],
      npm: ["node_modules"],
      ui: ["public/dist/"]
    },
    copy: {
      ui: {
        files: [
          {
            expand: true,
            cwd: "node_modules/bootstrap/dist/css",
            src: [
              "bootstrap.css",
              "bootstrap.min.css",
              "bootstrap.css.map",
              "bootstrap.min.css.map",
            ],
            dest: "public/dist/bootswatch/bootstrap/",
            filter: "isFile",
          },
          {
            expand: true,
            cwd: "node_modules/bootstrap/dist/js",
            src: [
              "bootstrap.bundle.js",
              "bootstrap.bundle.min.js",
              "bootstrap.bundle.js.map",
              "bootstrap.bundle.min.js.map",
            ],
            dest: "public/dist/bootstrap/",
            filter: "isFile",
          },
          {
            expand: true,
            cwd: "node_modules/bootstrap-icons/font",
            src: ["fonts/**", "bootstrap-icons.css"],
            dest: "public/dist/bootstrap-icons/",
            filter: "isFile",
          },
          {
            expand: true,
            cwd: "node_modules/bootstrap-select/dist/css",
            src: "**",
            dest: "public/dist/bootstrap-select/",
            filter: "isFile",
          },
          {
            expand: true,
            cwd: "node_modules/bootstrap-select/dist/js",
            src: "**",
            dest: "public/dist/bootstrap-select/",
            filter: "isFile",
          },
          {
            expand: true,
            cwd: "node_modules/bootstrap-table/dist",
            src: [
              "bootstrap-table.css",
              "bootstrap-table.min.css",
              "bootstrap-table.js",
              "bootstrap-table.min.js",
            ],
            dest: "public/dist/bootstrap-table/",
            filter: "isFile",
          },
          {
            expand: true,
            cwd: "node_modules/bootswatch/dist",
            src: ["**/bootstrap.css", "**/bootstrap.min.css"],
            dest: "public/dist/bootswatch/",
            filter: "isFile",
          },
          {
            expand: true,
            cwd: "node_modules/clipboard/dist",
            src: "**",
            dest: "public/dist/clipboard/",
            filter: "isFile",
          },
          {
            expand: true,
            cwd: "node_modules/jasny-bootstrap/dist/css",
            src: "**",
            dest: "public/dist/jasny-bootstrap/",
            filter: "isFile",
          },
          {
            expand: true,
            cwd: "node_modules/jasny-bootstrap/dist/js",
            src: "**",
            dest: "public/dist/jasny-bootstrap/",
            filter: "isFile",
          },
          {
            expand: true,
            cwd: "node_modules/jquery/dist",
            src: ["jquery.js", "jquery.min.js", "jquery.min.map"],
            dest: "public/dist/jquery/",
            filter: "isFile",
          },
        ],
      },
      licenses: {
        files: [
          {
            expand: true,
            src: "LICENSE",
            dest: "public/license/",
            filter: "isFile",
          },
        ],
      },
    },
  });

  grunt.loadNpmTasks("grunt-contrib-clean");
  grunt.loadNpmTasks("grunt-contrib-copy");

  grunt.registerTask("default", ["clean:ui", "clean:licenses", "copy"]);
};
