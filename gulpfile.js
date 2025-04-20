// Gulp Build Script

import gulp from 'gulp'
import clean from 'gulp-clean'
import rev from 'gulp-rev'
import concat from 'gulp-concat'
import cleanCSS from 'gulp-clean-css'
import rename from 'gulp-rename'
import uglify from 'gulp-uglify'
import inject from 'gulp-inject'
import logStream from 'gulp-debug'
import series from 'stream-series'
import header from 'gulp-header'
import tar from 'gulp-tar'
import gzip from 'gulp-gzip'
import zip from 'gulp-zip'
import run from 'gulp-run'

import { Stream } from 'readable-stream'
const {finished, pipeline} = Stream.promises


var prod0js;
var prod1js;

// Clear and remove the resources folder
gulp.task('clean', function () {
    return pipeline(
        gulp.src('./public/resources', {read: false, allowEmpty: true}),
        clean()
    );
});

gulp.task('compress', function() {
    const sources = ['app*/**/*', 'public*/**/*', 'vendor*/**/*', '*.md', 'LICENSE', 'docker*', 'Dockerfile', '**/.htaccess', 'writable*/**/*', '.env'] ;
    gulp.src(sources, {encoding: false}).pipe(tar('opensourcepos.tar')).pipe(gulp.dest('dist'));
    return gulp.src(sources, {encoding: false}).pipe(zip('opensourcepos.zip')).pipe(gulp.dest('dist'));
});


gulp.task('update-licenses', function() {
    run('composer licenses --format=json --no-dev > public/license/composer.LICENSES').exec();
    run('npx license-report --only=prod --output=json --fields=name --fields=author --fields=homepage --fields=installedVersion --fields=licenseType > public/license/npm-prod.LICENSES').exec();
    run('npx license-report --only=dev --output=json --fields=name --fields=author --fields=homepage --fields=installedVersion --fields=licenseType > public/license/npm-dev.LICENSES').exec();
    return pipeline(gulp.src('LICENSE'),gulp.dest('public/license'));
});


// Copy the bootswatch styles into their own folder so OSPOS can select one from the collection
gulp.task('copy-bootswatch', function() {
    pipeline(gulp.src('./node_modules/bootswatch/cerulean/*.min.css'),gulp.dest('public/resources/bootswatch/cerulean'));
    pipeline(gulp.src('./node_modules/bootswatch/cosmo/*.min.css'),gulp.dest('public/resources/bootswatch/cosmo'));
    pipeline(gulp.src('./node_modules/bootswatch/cyborg/*.min.css'),gulp.dest('public/resources/bootswatch/cyborg'));
    pipeline(gulp.src('./node_modules/bootswatch/darkly/*.min.css'),gulp.dest('public/resources/bootswatch/darkly'));
    pipeline(gulp.src('./node_modules/bootswatch/flatly/*.min.css'),gulp.dest('public/resources/bootswatch/flatly'));
    pipeline(gulp.src('./node_modules/bootswatch/journal/*.min.css'),gulp.dest('public/resources/bootswatch/journal'));
    pipeline(gulp.src('./node_modules/bootswatch/lumen/*.min.css'),gulp.dest('public/resources/bootswatch/lumen'));
    pipeline(gulp.src('./node_modules/bootswatch/paper/*.min.css'),gulp.dest('public/resources/bootswatch/paper'));
    pipeline(gulp.src('./node_modules/bootswatch/readable/*.min.css'),gulp.dest('public/resources/bootswatch/readable'));
    pipeline(gulp.src('./node_modules/bootswatch/sandstone/*.min.css'),gulp.dest('public/resources/bootswatch/sandstone'));
    pipeline(gulp.src('./node_modules/bootswatch/simplex/*.min.css'),gulp.dest('public/resources/bootswatch/simplex'));
    pipeline(gulp.src('./node_modules/bootswatch/slate/*.min.css'),gulp.dest('public/resources/bootswatch/slate'));
    pipeline(gulp.src('./node_modules/bootswatch/spacelab/*.min.css'),gulp.dest('public/resources/bootswatch/spacelab'));
    pipeline(gulp.src('./node_modules/bootswatch/superhero/*.min.css'),gulp.dest('public/resources/bootswatch/superhero'));
    pipeline(gulp.src('./node_modules/bootswatch/united/*.min.css'),gulp.dest('public/resources/bootswatch/united'));
    pipeline(gulp.src('./node_modules/bootswatch/yeti/*.min.css'),gulp.dest('public/resources/bootswatch/yeti'));
    return pipeline(gulp.src('./node_modules/bootswatch/fonts/*.*', {encoding:false}),gulp.dest('public/resources/bootswatch/fonts'));
});

// Copy the bootswatch styles into their own folder so OSPOS can select one from the collection
gulp.task('copy-bootswatch5', function() {
    pipeline(gulp.src('./node_modules/bootswatch5/dist/cerulean/*.min.css'),gulp.dest('public/resources/bootswatch5/cerulean'));
    pipeline(gulp.src('./node_modules/bootswatch5/dist/cosmo/*.min.css'),gulp.dest('public/resources/bootswatch5/cosmo'));
    pipeline(gulp.src('./node_modules/bootswatch5/dist/cyborg/*.min.css'),gulp.dest('public/resources/bootswatch5/cyborg'));
    pipeline(gulp.src('./node_modules/bootswatch5/dist/darkly/*.min.css'),gulp.dest('public/resources/bootswatch5/darkly'));
    pipeline(gulp.src('./node_modules/bootswatch5/dist/flatly/*.min.css'),gulp.dest('public/resources/bootswatch5/flatly'));
    pipeline(gulp.src('./node_modules/bootswatch5/dist/journal/*.min.css'),gulp.dest('public/resources/bootswatch5/journal'));
    pipeline(gulp.src('./node_modules/bootswatch5/dist/lumen/*.min.css'),gulp.dest('public/resources/bootswatch5/lumen'));
    pipeline(gulp.src('./node_modules/bootswatch5/dist/sandstone/*.min.css'),gulp.dest('public/resources/bootswatch5/sandstone'));
    pipeline(gulp.src('./node_modules/bootswatch5/dist/simplex/*.min.css'),gulp.dest('public/resources/bootswatch5/simplex'));
    pipeline(gulp.src('./node_modules/bootswatch5/dist/slate/*.min.css'),gulp.dest('public/resources/bootswatch5/slate'));
    pipeline(gulp.src('./node_modules/bootswatch5/dist/spacelab/*.min.css'),gulp.dest('public/resources/bootswatch5/spacelab'));
    pipeline(gulp.src('./node_modules/bootswatch5/dist/superhero/*.min.css'),gulp.dest('public/resources/bootswatch5/superhero'));
    pipeline(gulp.src('./node_modules/bootswatch5/dist/united/*.min.css'),gulp.dest('public/resources/bootswatch5/united'));
    return pipeline(gulp.src('./node_modules/bootswatch5/dist/yeti/*.min.css'),gulp.dest('public/resources/bootswatch5/yeti'));
});

// Copy the bootstrap style into its own folder so OSPOS can select it from the collection
gulp.task('copy-bootstrap', function() {
    pipeline(gulp.src('./node_modules/bootstrap/dist/css/bootstrap.min.css*'),gulp.dest('public/resources/bootswatch/bootstrap'));
    pipeline(gulp.src('./node_modules/bootstrap5/dist/css/bootstrap.min.css*'),gulp.dest('public/resources/bootswatch5/bootstrap'));
    return pipeline(gulp.src('./node_modules/bootstrap5/dist/css/bootstrap.rtl.min.css*'),gulp.dest('public/resources/bootswatch5/bootstrap'));
});

// /public/resources/ospos - contains the minimized files to be packed into opensourcepos.min.[css/js]
// /public/resources/[css/js] - contains the unpacked versions to be used in development mode
// /public/resources - contains the packed opensourcepos.min.[css/js] and the jquery.min.js

// Copy JavaScript into a folder which will be used as the source to create opensourcepos.min.js (except for jquery.mn.js)

// Inject will be in the sequence of the files in the stream.  So make sure dependencies are in their proper order


gulp.task('debug-js', function() {
    var debugjs = gulp.src(['./node_modules/jquery/dist/jquery.js',
        './node_modules/jquery-form/src/jquery.form.js',
        './node_modules/jquery-validation/dist/jquery.validate.js',
        './node_modules/jquery-ui-dist/jquery-ui.js',
        './node_modules/bootstrap/dist/js/bootstrap.js',
        './node_modules/bootstrap3-dialog/dist/js/bootstrap-dialog.js',
        './node_modules/jasny-bootstrap/dist/js/jasny-bootstrap.js',
        './node_modules/bootstrap-datetime-picker/js/bootstrap-datetimepicker.js',
        './node_modules/bootstrap-select/dist/js/bootstrap-select.js',
        './node_modules/bootstrap-table/dist/bootstrap-table.js',
        './node_modules/bootstrap-table/dist/extensions/export/bootstrap-table-export.js',
        './node_modules/bootstrap-table/dist/extensions/mobile/bootstrap-table-mobile.js',
        './node_modules/bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.js',
        './node_modules/moment/min/moment.min.js',
        './node_modules/bootstrap-daterangepicker/daterangepicker.js',
        './node_modules/es6-promise/dist/es6-promise.js',
        './node_modules/file-saver/dist/FileSaver.js',
        './node_modules/html2canvas/dist/html2canvas.js',
        './node_modules/jspdf/dist/jspdf.umd.js',
        './node_modules/jspdf-autotable/dist/jspdf.plugin.autotable.js',
        './node_modules/tableexport.jquery.plugin/tableExport.min.js',
        './node_modules/chartist/dist/chartist.js',
        './node_modules/chartist-plugin-pointlabels/dist/chartist-plugin-pointlabels.js',
        './node_modules/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.js',
        './node_modules/chartist-plugin-axistitle/dist/chartist-plugin-axistitle.js',
        './node_modules/chartist-plugin-barlabels/dist/chartist-plugin-barlabels.js',
        './node_modules/bootstrap-notify/bootstrap-notify.js',
        './node_modules/js-cookie/src/js.cookie.js',
        './node_modules/bootstrap-tagsinput-2021/dist/bootstrap-tagsinput.js',
        './node_modules/bootstrap-toggle/js/bootstrap-toggle.js',
        './node_modules/clipboard/dist/clipboard.js',
        './public/js/imgpreview.full.jquery.js',
        './public/js/manage_tables.js',
        './public/js/nominatim.autocomplete.js']).pipe(rev()).pipe(gulp.dest('public/resources/js'));
    return gulp.src('./app/Views/partial/header.php').pipe(inject(debugjs,{addRootSlash: false, ignorePath: '/public/', starttag: '<!-- inject:debug:js -->'})).pipe(gulp.dest('./app/Views/partial'));
});

gulp.task('prod-js', function() {

    var prod0js = gulp.src('./node_modules/jquery/dist/jquery.min.js').pipe(rev()).pipe(gulp.dest('public/resources'));

    var opensourcepos1js = gulp.src(['./node_modules/bootstrap/dist/js/bootstrap.min.js',
        './node_modules/bootstrap-table/dist/bootstrap-table.min.js',
        './node_modules/moment/min/moment.min.js',
        './node_modules/jquery-ui-dist/jquery-ui.min.js',
        './node_modules/bootstrap3-dialog/dist/js/bootstrap-dialog.min.js',
        './node_modules/jasny-bootstrap/dist/js/jasny-bootstrap.min.js',
        './node_modules/bootstrap-select/dist/js/bootstrap-select.min.js',
        './node_modules/bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.min.js',
        './node_modules/bootstrap-tagsinput-2021/dist/bootstrap-tagsinput.min.js',
        './node_modules/bootstrap-toggle/js/bootstrap-toggle.min.js',
        './node_modules/bootstrap-table/dist/extensions/export/bootstrap-table-export.min.js',
        './node_modules/bootstrap-table/dist/extensions/mobile/bootstrap-table-mobile.min.js',
        './node_modules/bootstrap-notify/bootstrap-notify.min.js',
        './node_modules/clipboard/dist/clipboard.min.js',
        './node_modules/jquery-form/dist/jquery.form.min.js',
        './node_modules/jquery-validation/dist/jquery.validate.min.js',
        './node_modules/bootstrap-datetime-picker/js/bootstrap-datetimepicker.min.js',
        './node_modules/es6-promise/dist/es6-promise.min.js',
        './node_modules/file-saver/dist/FileSaver.min.js',
        './node_modules/file-saver/dist/FileSaver.js',
        './node_modules/html2canvas/dist/html2canvas.min.js',
        './node_modules/chartist/dist/chartist.min.js',
        './node_modules/jspdf/dist/jspdf.umd.min.js',
        './node_modules/chartist/dist/chartist.min.js',
        './node_modules/chartist-plugin-pointlabels/dist/chartist-plugin-pointlabels.min.js',
        './node_modules/chartist-plugin-axistitle/dist/chartist-plugin-axistitle.min.js',
        './node_modules/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js',
        './node_modules/chartist-plugin-barlabels/dist/chartist-plugin-barlabels.min.js',
        './node_modules/tableexport.jquery.plugin/tableExport.min.js'], { allowEmpty: true });

    var opensourcepos2js = gulp.src(['./node_modules/bootstrap-daterangepicker/daterangepicker.js',
        './node_modules/js-cookie/src/js.cookie.js',
        './public/js/imgpreview.full.jquery.js',
        './public/js/manage_tables.js',
        './public/js/nominatim.autocomplete.js']).pipe(uglify());


    var prod1js = series(opensourcepos1js, opensourcepos2js).pipe(concat('opensourcepos.min.js'))
        .pipe(rev())
        .pipe(gulp.dest('./public/resources/'));

    return gulp.src('./app/Views/partial/header.php').pipe(inject(
        series(prod0js, prod1js), {addRootSlash: false, ignorePath: '/public/', starttag: '<!-- inject:prod:js -->'})).pipe(gulp.dest('./app/Views/partial'));

});



gulp.task('debug-css', function() {
    var debugcss = gulp.src(['./node_modules/jquery-ui-dist/jquery-ui.css',
        './node_modules/bootstrap3-dialog/dist/css/bootstrap-dialog.css',
        './node_modules/jasny-bootstrap/dist/css/jasny-bootstrap.css',
        './node_modules/bootstrap-datetime-picker/css/bootstrap-datetimepicker.css',
        './node_modules/bootstrap-select/dist/css/bootstrap-select.css',
        './node_modules/bootstrap-table/dist/bootstrap-table.css',
        './node_modules/bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.css',
        './node_modules/bootstrap-daterangepicker/daterangepicker.css',
        './node_modules/chartist/dist/chartist.css',
        './node_modules/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.css',
        './node_modules/bootstrap-tagsinput-2021/src/bootstrap-tagsinput.css',
        './node_modules/bootstrap-toggle/css/bootstrap-toggle.css',
        './public/css/bootstrap.autocomplete.css',
        './public/css/invoice.css',
        './public/css/ospos_print.css',
        './public/css/ospos.css',
        './public/css/popupbox.css',
        './public/css/receipt.css',
        './public/css/register.css',
        './public/css/reports.css'
    ]).pipe(rev()).pipe(gulp.dest('public/resources/css'));
    return gulp.src('./app/Views/partial/header.php').pipe(inject(debugcss,{addRootSlash: false, ignorePath: '/public/', starttag: '<!-- inject:debug:css -->'})).pipe(gulp.dest('./app/Views/partial'));
});


gulp.task('prod-css', function() {
    var opensourcepos1css = gulp.src(['./node_modules/jquery-ui-dist/jquery-ui.min.css',
        './node_modules/bootstrap3-dialog/dist/css/bootstrap-dialog.min.css',
        './node_modules/jasny-bootstrap/dist/css/jasny-bootstrap.min.css',
        './node_modules/bootstrap-datetime-picker/css/bootstrap-datetimepicker.min.css']);

    var opensourcepos2css = gulp.src(['./node_modules/bootstrap-daterangepicker/daterangepicker.css',
        './node_modules/bootstrap-tagsinput-2021/src/bootstrap-tagsinput.css']).pipe(cleanCSS({compatibility: 'ie8'}));

    var opensourcepos3css = gulp.src(['./node_modules/bootstrap-select/dist/css/bootstrap-select.min.css',
        './node_modules/bootstrap-table/dist/bootstrap-table.min.css',
        './node_modules/bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.min.css',
        './node_modules/bootstrap-toggle/css/bootstrap-toggle.min.css',
        './node_modules/chartist/dist/chartist.min.css']);

    var opensourcepos4css = gulp.src('./node_modules/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.css').pipe(cleanCSS({compatibility: 'ie8'}));

    var opensourcepos5css = gulp.src(['./node_modules/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.css',
        './public/css/bootstrap.autocomplete.css',
        './public/css/invoice.css',
        './public/css/ospos.css',
        './public/css/ospos_print.css',
        './public/css/popupbox.css',
        './public/css/receipt.css',
        './public/css/register.css',
        './public/css/reports.css'
    ]).pipe(cleanCSS({compatibility: 'ie8'}));

    var prodcss = series(opensourcepos1css, opensourcepos2css, opensourcepos3css, opensourcepos4css, opensourcepos5css)
        .pipe(concat('opensourcepos.min.css')).pipe(rev()).pipe(gulp.dest('public/resources'));


    return gulp.src('./app/Views/partial/header.php').pipe(inject(prodcss,{addRootSlash: false, ignorePath: '/public/', starttag: '<!-- inject:prod:css -->'})).pipe(gulp.dest('./app/Views/partial'));
});


gulp.task('copy-fonts', function() {
    return pipeline(gulp.src('./node_modules/bootstrap/dist/fonts/glyphicons-halflings-regular.*', {encoding: false}),rev(),gulp.dest('public/resources'));
});


gulp.task('copy-menubar', function() {
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/star.svg"),rename("attributes.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/bookshelf.svg"),rename("cashups.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/gear.svg"),rename("config.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/contacts.svg"),rename("customers.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/profle.svg"),rename("employees.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/compose.svg"),rename("expenses.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/clipboard.svg"),rename("expenses_categories.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/heart.svg"),rename("giftcards.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/door.svg"),rename("home.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/stack.svg"),rename("item_kits.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/shop.svg"),rename("items.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/smartphone.svg"),rename("messages.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/tools.svg"),rename("migrate.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/door.svg"),rename("office.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/dolly.svg"),rename("receivings.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/bar-chart.svg"),rename("reports.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/cart.svg"),rename("sales.svg"),gulp.dest("public/images/menubar"));
    pipeline(gulp.src("./node_modules/elegant-circles/svg/full-color/briefcase.svg"),rename("suppliers.svg"),gulp.dest("public/images/menubar"));
    return pipeline(gulp.src('./node_modules/elegant-circles/svg/full-color/money.svg'),rename("taxes.svg"),gulp.dest("public/images/menubar"));
});


gulp.task('build-database', function() {
    return gulp.src(['./app/Database/tables.sql','./app/Database/constraints.sql'])
        .pipe(header('-- >> This file is autogenerated from tables.sql and constraints.sql. Do not modify directly << --'))
        .pipe(concat('database.sql'))
        .pipe(gulp.dest('./app/Database'));
});

// Run all required tasks
gulp.task('default',
    gulp.series('clean',
        'update-licenses',
        'copy-bootswatch',
        'copy-bootswatch5',
        'copy-bootstrap',
        'debug-js',
        'prod-js',
        'debug-css',
        'prod-css',
        'copy-fonts',
        'copy-menubar',
        'build-database'
    ));
