# frozen_string_literal: true

def read_iu_submissions_s3_path # Use dotenv or sth if more than this is ever read
  result = `grep S3_COPIES_BUCKET_URL .env.local | cut -f2 -d'='`.strip!
  result += '/' unless result.end_with?('/')

  result
end

IMPORT_DIR_PATH = 'var/iuFormData/' # Trailing slash required

DB_PATH = 'var/db.sqlite'
DB_TMP_PATH = "#{DB_PATH}.tmp"

DB_DUMP_DIR_PATH = 'db_dump'
DB_DUMP_TMP_PATH = "#{DB_DUMP_DIR_PATH}/fuzzrake.tmp.sql"
DB_DUMP_PRV_COPY_PATH = "#{DB_DUMP_DIR_PATH}/artisans_private_data-#{Time.now.getutc.strftime('%Y-%m-%d_%H-%M-%S')}.sql"

# Volatile information, easily reproducible
IGNORED_TABLES = %w[
  artisans_commissions_statuses
  artisans_volatile_data
  artisans_urls_states
  tracker_settings
  submissions
].freeze

#
# HELPER FUNCTIONS
#

def mtask(the_task, called_task, *additional_args)
  task(the_task) do |_t, args|
    Rake::Task[called_task].invoke(*additional_args, *args)
  end
end

def run_shell(*args)
  print("Executing: '#{args.join("' '")}'\n")
  system(*args) || raise('Command returned non-zero exit code')
end

def docker_compose(*args)
  project_name = ENV.fetch('FUZZRAKE_DEV_PROJECT_NAME', 'fuzzrake')

  run_shell('docker', 'compose', '--project-directory', 'docker', '--project-name', project_name, *args)
end

def run_docker(*args)
  user_and_group = `echo -n $(id -u):$(id -g)`

  docker_compose('exec', '--user', user_and_group, '-ti', 'php', *args)
end

def run_console(*args)
  run_docker('./bin/console', *args)
end

def run_composer(*args)
  run_docker('composer', *args)
end

def clear_cache
  run_shell('sudo', 'rm', '-rf', 'var/cache/dev', 'var/cache/test')
end

#
# MISCELLANEOUS TASKS
#

task(:default)  { run_shell('rake', '--tasks', '--all') }
task(:console)  { |_t, args| run_console(*args) }
task(:cc)       { clear_cache }
task(:cl)       { run_shell('sudo', 'truncate', '-s0', 'var/log/dev.log', 'var/log/test.log') }
task('cc-prod') { run_shell('ssh', 'getfursu.it', 'sudo rm -rf /var/www/prod/var/cache/prod') }
task(:composer) { |_t, args| run_composer(*args) }
task(:docker)   { |_t, args| run_docker(*args) }

#
# TESTING AND DEV
#

def create_link(file_path, link_path)
  Dir.chdir(File.dirname(link_path)) do
    link_name = File.basename(link_path)
    add_dirs = '../' * link_path.count('/')

    File.delete(link_name) if File.symlink?(link_name)
    File.symlink(add_dirs + file_path, link_name)
  end
end

def phpunit(*additional_args)
  run_docker('./bin/phpunit', '--testdox', *additional_args)
end

def fix_phpunit
  create_link('vendor/symfony/phpunit-bridge', 'vendor/bin/.phpunit/phpunit/vendor/symfony/phpunit-bridge')
end

task('fix-phpunit')  { fix_phpunit }
task('docker-up')    { docker_compose('up', '--detach', '--build') }
task('docker-down')  { docker_compose('down') }
task(:rector)        { |_t, args| run_docker('./vendor/bin/rector', 'process', *args) }
task(:phpstan)       { |_t, args| run_docker('./vendor/bin/phpstan', 'analyse', '-c', 'phpstan.neon', *args) }
task('php-cs-fixer') { |_t, args| run_docker('./vendor/bin/php-cs-fixer', 'fix', *args) }
task(:phpunit)       { |_t, args| phpunit(*args) }

task pcf: ['php-cs-fixer']
task pu: [:phpunit]
mtask(:pus, :phpunit, '--group', 'small')
mtask(:pum, :phpunit, '--group', 'medium')
mtask(:pul, :phpunit, '--group', 'large')
task ps: [:phpstan]

#
# DATABASE MANAGEMENT
#

task :dbpush do
  run_shell('cp', DB_PATH, DB_TMP_PATH)

  run_shell('sqlite3', DB_TMP_PATH, "UPDATE artisans_private_data SET original_contact_info = '', contact_address = '';")

  run_shell('scp', '-p', DB_TMP_PATH, "getfursu.it:/var/www/prod/#{DB_PATH}")
  run_shell('scp', '-p', DB_TMP_PATH, "getfursu.it:/var/www/beta/#{DB_PATH}")

  run_shell('rm', DB_TMP_PATH)
end

def backup_private_data
  run_shell('sqlite3', DB_PATH, ".output #{DB_DUMP_PRV_COPY_PATH}", '.dump artisans_private_data')
end

task :dbpull do
  run_shell('scp', '-p', "getfursu.it:/var/www/prod/#{DB_PATH}", DB_TMP_PATH)
  backup_private_data
  run_shell('sqlite3', DB_TMP_PATH, 'DROP TABLE artisans_private_data;')
  run_shell('sqlite3', DB_TMP_PATH, ".read #{DB_DUMP_PRV_COPY_PATH}")
  run_shell('chmod', 'a+w', DB_TMP_PATH)
  run_shell('mv', DB_TMP_PATH, DB_PATH)
end

task :dbdump do
  table_names = `sqlite3 #{DB_PATH} .tables`.split(/\s+/)

  IGNORED_TABLES.each do |table_name| # Sanity check
    raise "#{table_name} does not exist in the DB #{DB_PATH}" unless table_names.include?(table_name)
  end

  backup_private_data

  table_names.each do |table_name|
    next if IGNORED_TABLES.include?(table_name)

    run_shell('sqlite3', DB_PATH, ".output #{DB_DUMP_DIR_PATH}/#{table_name}.sql", ".dump #{table_name}")
  end
end

task :dbcommit do
  Dir.chdir(DB_DUMP_DIR_PATH) do
    run_shell('git', 'reset', 'HEAD')
    run_shell('git', 'commit', '-m', 'Updated DB dump', '-p')
    run_shell('git', 'push')
    run_shell('git', 'show', '-q')
  end
end

#
# RELEASES MANAGEMENT
#

task('release-beta') do
  run_shell('git', 'branch', '-D', 'beta')
  run_shell('git', 'checkout', '-b', 'beta')
  run_shell('git', 'push', '--force', 'origin', 'beta')
  run_shell('ansible/setup_envs.yaml', '--limit', 'beta_env')

  print("Make sure to return to the previous branch\n") # FIXME: This stupid limitation
end

task('release-prod') do
  run_shell('git', 'checkout', 'main')
  run_shell('git', 'merge', '--no-edit', 'develop')
  run_shell('git', 'push')
  run_shell('git', 'checkout', 'develop')
  run_shell('git', 'merge', 'main')
  run_shell('git', 'push')
  run_shell('ansible/setup_envs.yaml', '--limit', 'prod_env')
end

task(:composer_upgrade) { run_docker('composer', '--no-cache', 'upgrade') } # No cache in the container
task(:yarn_upgrade) { run_shell('yarn', 'upgrade') }
task(:yarn_encore_production) { run_shell('yarn', 'encore', 'production') }
task 'update-deps': [:composer_upgrade, :yarn_upgrade, :yarn_encore_production, 'fix-phpunit'] do
  clear_cache
end
task('commit-deps') { run_shell('git', 'commit', '-m', 'Updated 3rd party dependencies', 'composer.lock', 'symfony.lock', 'yarn.lock') }

task yep: [:yarn_encore_production]

#
# COMMISSIONS STATUS UPDATES
#

task 'get-snapshots' do
  run_shell('rsync', '--recursive', '--progress', '--human-readable', '--compress', '--checksum',
            'getfursu.it:/var/www/prod/var/snapshots/', 'var/snapshots/')
end

mtask(:cst, :console, 'app:status-tracker:run')
mtask(:cstc, :cst, '--commit')
mtask(:cstr, :cst, '--refetch')

#
# IMPORT TASKS
#

task 'get-submissions' do
  run_shell('rsync', '--recursive', '--progress', '--human-readable', '--compress', '--checksum',
            'getfursu.it:/var/www/prod/var/iuFormData/', IMPORT_DIR_PATH)
  run_shell('aws', 's3', 'sync', '--size-only', read_iu_submissions_s3_path, IMPORT_DIR_PATH)
end

#
# DATA TIDY TASKS
#

mtask(:tidy, :console, 'app:data:tidy')
mtask(:tidyc, :tidy, '--commit')
