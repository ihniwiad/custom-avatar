require( 'dotenv' ).config();
const envConfig = process.env;

const { src, dest, watch, series, parallel } = require( 'gulp' );
const rename = require( 'gulp-rename' );
const clean = require( 'gulp-clean' );

const fs = require( 'fs' );
const replace = require( 'gulp-string-replace' );


const paths = {
    publish: {
        watchSrc: [ 
            '*.php',
            'languages/*.mo',
        ],
    },
};


// PUBLISH HOWTO: 
// If you like to copy your files to another folder after build make 
// `.env` file with content `FOLDER_NAME=your_folder_name` and `PUBLISH_PATH=path_to_your_folder`, 
// e.g.: 
// `FOLDER_NAME=my_project`
// `PUBLISH_PATH=../../../../../Applications/MAMP/htdocs/`
// Have a look at `publishConfig` which files to include / exclude
// and how to name your created destination folder
// 
// NOTE: within `src` all (1..n) non-negative globs must be followed by (0..n) only negative globs
const publishConfig = {
    "src": [
        "**/*",
        "!**/node_modules",
        "!**/node_modules/**", 
    ],
    "base": ".",
    "folderName": ( !! envConfig.FOLDER_NAME ? envConfig.FOLDER_NAME : '' )
};


// NOTE: take care at this path since you’re deleting files outside your project
const publishFullPath = envConfig.PUBLISH_PATH + publishConfig.folderName;


const publishFolderDelete = ( cb ) => {

    if ( !! envConfig.PUBLISH_PATH && !! publishConfig.folderName ) {
        console.log( 'delete: ' + publishFullPath );
        return src( publishFullPath, { read: false, allowEmpty: true } )
            .pipe( clean( { force: true } ) ) // NOTE: take care at this command since you’re deleting files outside your project
        ;
    }
    else {
        // do nothing
    }

    cb();
}

const publishFolderCreate = ( cb ) => {

    if ( !! envConfig.PUBLISH_PATH && !! publishConfig.folderName ) {
        // console.log( 'src: ' + publishConfig.src + ', base: ' + publishConfig.base );
        console.log( 'create: ' + publishFullPath );
        return src( publishConfig.src, { base: publishConfig.base } )
            .pipe( dest( publishFullPath ) )
        ;
    }
    else {
        // log note, do nothing
        console.log( 'Note: No publishing done since publish configuration empty.' );
    }

    cb();
}

const publish = series(
    // copy all project but `node_modules` to configured dest
    publishFolderDelete,
    publishFolderCreate,
);

exports.publish = publish;


function allWatch() {
    watch( paths.publish.watchSrc, publish );
}

exports.watch = allWatch;


const build = series(
    publish,
);

exports.build = build;




