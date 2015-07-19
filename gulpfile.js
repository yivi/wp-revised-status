// grab our gulp packages
var gulp = require('gulp'),
    util = require('gulp-util'),
    replace = require('gulp-replace'),
    bump = require('gulp-bump'),
    git = require('gulp-git'),
    filter = require('gulp-filter'),
    tag = require('gulp-tag-version'),
    fs = require('fs'),
    readme = require('gulp-readme-to-markdown')
dbg = require('gulp-debug');


var pkg = JSON.parse(fs.readFileSync('./package.json', 'utf8'));
var getPackageJson = function () {
    return pkg = JSON.parse(fs.readFileSync('./package.json', 'utf8'));
};

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

gulp.task('getPackageJson', function (cb) {
    pkg = getPackageJson();
    cb(err);
});


function inc(importance) {

    version = pkg.version.replace('v', '');
    nextV = bumpReadme(version, importance);

    onlyJson = filter('package.json');
    onlyReadme = filter('readme.txt');

    // get all the files to bump version in

    return gulp.src(['package.json', 'readme.txt', 'README.md' ])
        .pipe(onlyJson)
        // bump the version number in those files
        .pipe(bump({type: importance}))
        // save it back to filesystem
        .pipe(gulp.dest('./'))


        .pipe(onlyJson.restore())
        .pipe(dbg({title: 'After Json Restore'}))

        .pipe(onlyReadme)
        .pipe(dbg({title: 'After Readme Filter '}))
        .pipe(replace('Stable tag: ' + version, 'Stable tag: ' + nextV))
        .pipe(gulp.dest('./'))

        .pipe(readme())
        .pipe(dbg({title: 'After Readme Process '}))
        .pipe(gulp.dest('./'))


        // restore the original filter, so I add three files to git
        .pipe(onlyReadme.restore())

        .pipe(dbg({title: 'After Readme Restore'}))
        .pipe(git.add())

        // commit the changed version number
        .pipe(git.commit('bump to v' + nextV ))
        .pipe(git.tag('v' + nextV, 'Bumping from ' + version + 'to ' + nextV), function(err) {
            if (err) throw err;
        });

    // read only one file to get the version number
    // .pipe(filter('package.json'))
    // **tag it in the repository**
    // .pipe(tag);
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