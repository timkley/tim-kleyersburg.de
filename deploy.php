<?php
namespace Deployer;

require 'recipe/common.php';

/* Change variables to match your project */
$host = 'tim-kleyersburg.de';
$repository = 'git@gitlab.com:timkley/tim-kleyersburg.de.git';
$deploy_path = '/var/www/virtual/thenose/_deployment/tim-kleyersburg.de';

/* ------------------------------------------------------------------------ */
/* DON'T CHANGE ANYTHING BELOW THIS LINE UNLESS YOU KNOW WHAT YOU'RE DOING! */
/* ------------------------------------------------------------------------ */

// Project name
set('application', $host);

// Project repository
set('repository', $repository);

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
set('shared_files', []);
set('shared_dirs', []);

// Writable dirs by web server 
set('writable_dirs', []);
set('allow_anonymous_stats', false);

// Hosts

host($host)
    ->user('thenose')
    ->set('deploy_path', $deploy_path);

// Tasks

task('build', function() {
    run('npm i');
    run('npm run build');
})->local();

task('upload', function() {
    upload(__DIR__ . "/", '{{release_path}}');
});

task('release', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'upload',
    'deploy:shared',
    'deploy:writable',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
]);

task('deploy', [
    'build',
    'release',
    'cleanup',
    'success'
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');