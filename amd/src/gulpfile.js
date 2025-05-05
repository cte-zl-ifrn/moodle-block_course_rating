const gulp = require('gulp')
const sass = require('gulp-sass')(require('sass'))
const postcss = require('gulp-postcss')
const concat = require('gulp-concat')
const autoprefixer = require('autoprefixer')
const babel = require('gulp-babel')
const uglify = require('gulp-uglify')

function compileSass(){
    return gulp
        .src('styles/*.scss')
            .pipe(sass({
            style: 'compressed'
        }))
        .pipe(
            postcss([
            autoprefixer({
            cascade: false
        })]))
        .pipe(gulp.dest('../../'))
}

function compileJs(){
    return gulp
        .src('scripts/*.js')
        .pipe(concat('rating.min.js'))
        .pipe(babel({
                presets: ['@babel/env']
            }))
        .pipe(uglify())
        .pipe(gulp.dest('../build/'))
}

exports.sass = compileSass
exports.gulpJS = compileJs

exports.default =  gulp.parallel(compileSass, compileJs)