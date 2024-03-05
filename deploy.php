<?php

namespace Deployer;

require 'recipe/craftcms.php';

set('application', 'craft-starter');
set('repository', 'git@github.com:znezniV/craft-starter.git');

set('git_ssh_command', 'ssh'); // Fixes: https://github.com/deployphp/deployer/issues/2908#issuecomment-1022748724
set('allow_anonymous_stats', false); // Do not share anonymous data

set('shared_files', ['.env']);

set('http_user', 'www-data');
set('writable_mode', 'chmod');

host('staging')
  ->set('hostname', 'staging.web.craft-starter.dev')
  ->set('remote_user', 'johanne')
  ->set('forward_agent', true)
  ->set('port', 2121)
  ->set('branch', 'staging')
  ->set('labels', ['stage' => 'staging'])
  ->set('deploy_path', '/www/staging');

host('production')
  ->set('hostname', 'craft-starter.dev')
  ->set('remote_user', 'johanne')
  ->set('forward_agent', true)
  ->set('port', 2121)
  ->set('branch', 'main')
  ->set('labels', ['stage' => 'production'])
  ->set('deploy_path', '/www/production');

// link storage
task('craft:storage-link', function () {
    run("cd {{release_path}} && composer run storage:link");
});

desc('Build and upload vite assets');
task('build:vite', function () {
  runLocally('npm install');
  runLocally('npm run build');
  upload('web/dist', '{{release_path}}/web');
})->once();

desc('Execute craft db/backup');
task('craft:db/backup', craft('db/backup'))->once();

after('deploy:vendors', 'build:vite');
after('deploy:vendors', 'craft:db/backup');

after('deploy:failed', 'deploy:unlock');
