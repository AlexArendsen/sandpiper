module.exports = function(grunt){
	grunt.initConfig({
		cssmin: {
			css: {
				files: {
					'build/sandpiper.min.css': ['src/css/*.css']
				}
			}
		},
		uglify: {
			js: {
				files: {
					'build/sandpiper.min.js': ['src/js/init.js','src/js/**/*.js']
				}
			}
		},
		concat: {
			js: {
				src: ['src/js/init.js','src/js/**/*.js'],
				dest: 'build/sandpiper.min.js'
			}
		},
		wiredep: {
			base: {
				src: ['views/base.html']
			}
		},
		watch: {
			watchJS: {
				files: ['src/js/**/*.js'],
				tasks: ['concat']
			},
			watchCSS: {
				files: ['src/css/*.css'],
				tasks: ['cssmin']
			}
		}
	})

	grunt.loadNpmTasks('grunt-contrib-cssmin')
	grunt.loadNpmTasks('grunt-contrib-uglify')
	grunt.loadNpmTasks('grunt-contrib-concat')
	grunt.loadNpmTasks('grunt-contrib-watch')
	grunt.loadNpmTasks('grunt-wiredep')

	grunt.registerTask('default', ['cssmin','uglify','wiredep','watch'])
}
