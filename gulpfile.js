'use strict';

var gulp = require('gulp');

var testTask = require('./gulp-tasks/karma-unit-test.js');

/**
 * Runs Karma unit tests
 */
gulp.task('test', gulp.series(testTask));

/**
 * Runs test task
 */
gulp.task('default', gulp.parallel('test'));
