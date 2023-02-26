module.exports = function(grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		bower_concat: {
			all: {
				mainFiles: {
					'bootstrap-table': [
						'dist/bootstrap-table.min.js',
						'dist/bootstrap-table.css',
						'dist/extensions/export/bootstrap-table-export.min.js',
						'dist/extensions/mobile/bootstrap-table-mobile.min.js',
						'dist/extensions/sticky-header/bootstrap-table-sticky-header.min.js',
						'dist/extensions/sticky-header/bootstrap-table-sticky-header.css'
					],
					'chartist-plugin-axistitle': [ "./dist/chartist-plugin-axistitle.min.js"]
				},
				dest: {
					'js': '../../tmp/opensourcepos_bower.js',
					'css': '../../tmp/opensourcepos_bower.css'
				}
			}
		},
		copy: {
			themes: {
				files: [
					{
						expand: true,
						cwd: '../node_modules/bootstrap/dist/css',
						src: ['bootstrap.css', 'bootstrap.min.css'],
						dest: '../public/dist/bootswatch-5/bootstrap/',
						filter: 'isFile'
					},
					{
						expand: true,
						cwd: '../node_modules/bootswatch/dist',
						src: ['**/bootstrap.css', '**/bootstrap.min.css'],
						dest: '../public/dist/bootswatch-5/',
						filter: 'isFile'
					}
				],
			},
			licenses: {
				files: [{
					expand: true,
					src: 'LICENSE',
					dest: 'public/license/',
					filter: 'isFile',},
					{
						expand: true,
						cwd: '../node_modules/bootstrap',
						src: 'LICENSE',
						dest: '../public/license/',
						rename: function(dest, src) { return dest + src.replace('LICENSE', 'bootstrap-5.license'); },
						filter: 'isFile'
					},
					{
						expand: true,
						cwd: '../node_modules/bootswatch',
						src: 'LICENSE',
						dest: '../public/license/',
						rename: function(dest, src) { return dest + src.replace('LICENSE', 'bootswatch-5.license'); },
						filter: 'isFile'
					},
				],
			},
		},
		cachebreaker: {
			dev: {
				options: {
					match: [ {
						'opensourcepos.min.js': '../public/dist/opensourcepos.min.js',
						'opensourcepos.min.css': '../public/dist/opensourcepos.min.css'
					} ],
					replacement: 'md5'
				},
				files: {
					src: ['../app/Views/partial/header.php', '../app/Views/login.php']
				}
			}
		},
		clean: {
			options: {
				force: true
			},
			bower: ["../public/resources"],
			composer: ["../vendor"],
			license: ['../public/resources/**/bower.json'],
			npm: ["../node_modules"]
		},
		license: {
			all: {
				options: {
					directory: '../public/resources',
					output: '../public/license/bower.LICENSES'
				}
			}
		},
		'bower-licensechecker': {
			options: {
				acceptable: [ 'MIT', 'BSD', 'LICENSE.md' ],
				printTotal: true,
				warn: {
					nonBower: true,
					noLicense: true,
					allGood: true,
					noGood: true
				},
				log: {
					outFile: '../public/license/.licenses',
					nonBower: true,
					noLicense: true,
					allGood: true,
					noGood: true,
				}
			}
		},
	});

	require('load-grunt-tasks')(grunt);

	grunt.loadNpmTasks('grunt-bower-concat');

	grunt.registerTask('task2', ['bower_concat']);
	grunt.registerTask('task4', ['copy']);
	grunt.registerTask('task6', ['cachebreaker', 'clean:license', 'license', 'bower-licensechecker']);

};
