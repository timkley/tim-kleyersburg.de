<?php
namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'tim-kleyersburg.de');

// Project repository
set('repository', 'git@gitlab.com:timkley/tim-kleyersburg.de.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
set('shared_files', []);
set('shared_dirs', []);

// Writable dirs by web server 
set('writable_dirs', []);
set('allow_anonymous_stats', false);

// Hosts

host('us.timkley.de')
    ->user('thenose')
    ->set('deploy_path', '/var/www/virtual/thenose/_deployment/tim-kleyersburg.de');

// Tasks

task('build', function() {
    run('yarn');
    run('./node_modules/.bin/gulp default');
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