// grab our gulp packages
var gulp = require('gulp'),
    util = require('gulp-util'),
    replace = require('gulp-replace'),
    bump = require('gulp-bump'),
    git = require('gulp-git'),
    filter = require('gulp-filter'),
    fs = require('fs'),
    readme = require('gulp-readme-to-markdown'),
    merge = require('merge-stream'),
    dbg = require('gulp-debug');


var bumpReadme = function (version, type) {
    util.log('Starting to bump from ' + version);
    version = version.split('.');
    switch (type) {
        case 'patch':
            patch = parseInt(version[2]);
            patch++;
            version[2] = patch.toString();
            break;
        case 'minor':
            minor = parseInt(version[1]);
            minor++;
            version[1] = minor.toString();
            version[2] = '0';
            break;
        case 'major':
            major = parseInt(version[0]);
            major++;
            version[0] = major.toString();
            version[1] = 0;
            version[2] = 0;
            break;
    }

    version = version.join('.');
    util.log('Finisthing bump to ' + version);

    return version;
};


function inc(importance) {

    var pkg = JSON.parse(fs.readFileSync('./package.json', 'utf8'));
    var version = pkg.version.replace('v', '');
    var nextV = bumpReadme(version, importance);

    onlyJson = filter('package.json');
    onlyReadme = filter('readme.txt');

    // get all the files to bump version in

    readme = gulp.src('readme.txt')
        .pipe(replace('Stable tag: ' + version, 'Stable tag: ' + nextV))
        .pipe(gulp.dest('./'))
        .pipe(readme({
            screenshot_url: 'https://ps.w.org/revised-publishing-status/assets/{screenshot}.{ext}'
        }))
        .pipe(gulp.dest('./'));

    json = gulp.src('package.json')
        .pipe(bump({type: importance}))
        .pipe(gulp.dest('./'));

    plugin = gulp.src('init.php')
        .pipe(replace('Version: ' + version, 'Version: ' + nextV))
        .pipe(gulp.dest('./'));

    readmeme = gulp.src(['README.md', 'readme.txt']);

    return merge([json, readmeme])
        .pipe(git.add())
        // commit the changed version number
        .pipe(git.commit('bump to v' + nextV))
        .pipe(git.tag('v' + nextV, 'Bumping from ' + version + 'to ' + nextV), function (err) {
            if (err) throw err;
        });

}

function tagSvn() {
    // elijo todos los ficheros desde el trunk
    // excluyendo .git, assets, node_modules, .idea

}

gulp.task('patch', function () {
    return inc('patch');
});
gulp.task('feature', function () {
    return inc('minor');
});
gulp.task('release', function () {
    return inc('major');
});


// create a default task and just log a message
gulp.task('default', function () {
    return
});