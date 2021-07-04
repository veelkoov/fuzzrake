# frozen_string_literal: true

def read_iu_submissions_s3_path
  # TODO: dotenv
  result = `grep S3_COPIES_BUCKET_URL .env.local | cut -f2 -d'='`.strip!
  result += '/' unless result.end_with?('/')

  result
end

def create_link(file_path, link_path)
  Dir.chdir(File.dirname(link_path)) do
    link_name = File.basename(link_path)
    add_dirs = '../' * link_path.count('/')

    File.delete(link_name) if File.symlink?(link_name)
    File.symlink(add_dirs + file_path, link_name)
  end
end

IMPORT_DIR_PATH = 'var/iuFormData/' # Trailing slash required
FIXES_FILE_PATH = 'imports/import-fixes.txt'
IU_SUBMISSIONS_S3_PATH = read_iu_submissions_s3_path

DB_PATH = 'var/db.sqlite'
DB_TMP_PATH = "#{DB_PATH}.tmp"

DB_DUMP_DIR_PATH = 'db_dump'
DB_DUMP_TMP_PATH = "#{DB_DUMP_DIR_PATH}/fuzzrake.tmp.sql"
DB_DUMP_PRV_COPY_PATH = "#{DB_DUMP_DIR_PATH}/artisans_private_data-#{Time.now.getutc.strftime('%Y-%m-%d_%H-%M-%S')}.sql"

# Volatile information, easily reproducible
IGNORED_TABLES = %w[
  artisans_commissions_statues
  artisans_urls_states
].freeze

#
# HELPER FUNCTIONS
#

def mtask(the_task, called_task, *additional_args)
  task(the_task) do |_t, args|
    Rake::Task[called_task].invoke(*additional_args, *args)
  end
end

def exec_or_die(*args)
  print("Executing: '#{args.join("' '")}'\n")
  system(*args) || raise('Command returned non-zero exit code')
end

def docker(*args)
  user_and_group = `echo -n $(id -u):$(id -g)`

  exec_or_die('docker', 'exec', '--user', user_and_group, '-ti', 'fuzzrake', *args)
end

task(:console) { |_t, args| docker('./bin/console', *args) }

#
# MISCELLANEOUS TASKS
#

task(:default)       { exec_or_die('rake', '--tasks', '--all') }
mtask(:cc, :console, 'cache:clear')

#
# TESTING AND DEV
#
task('fix-phpunit') do
  create_link('vendor/symfony/phpunit-bridge/bin/simple-phpunit', 'bin/.phpunit/phpunit/bin/simple-phpunit')
  create_link('vendor/symfony/phpunit-bridge', 'bin/.phpunit/phpunit/vendor/symfony/phpunit-bridge')
end
task('docker-dev') { Dir.chdir('docker') { exec_or_die('docker-compose', 'up', '--detach', '--build') } }
task(:rector)        { |_t, args| docker('./vendor/bin/rector', 'process', *args) }
task(:php_cs_fixer)  { |_t, args| docker('./vendor/bin/php-cs-fixer', 'fix', *args) }
task(:phpunit)       { |_t, args| docker('xvfb-run', './bin/phpunit', *args) }
task qa: [:rector, 'php-cs-fixer', :phpunit]

task pcf: [:php_cs_fixer]
task pu: [:phpunit]

#
# DATABASE MANAGEMENT
#

task :dbpush do
  exec_or_die('cp', DB_PATH, DB_TMP_PATH)

  exec_or_die('sqlite3', DB_TMP_PATH, "UPDATE artisans_private_data SET original_contact_info = '', contact_address = '';")

  exec_or_die('scp', '-p', DB_TMP_PATH, "getfursu.it:/var/www/prod/#{DB_PATH}")
  exec_or_die('scp', '-p', DB_TMP_PATH, "getfursu.it:/var/www/beta/#{DB_PATH}")

  exec_or_die('rm', DB_TMP_PATH)
end

def backup_private_data
  exec_or_die('sqlite3', DB_PATH, ".output #{DB_DUMP_PRV_COPY_PATH}", '.dump artisans_private_data')
end

task :dbpull do
  exec_or_die('scp', '-p', "getfursu.it:/var/www/prod/#{DB_PATH}", DB_TMP_PATH)
  backup_private_data
  exec_or_die('sqlite3', DB_TMP_PATH, 'DROP TABLE artisans_private_data;')
  exec_or_die('sqlite3', DB_TMP_PATH, ".read #{DB_DUMP_PRV_COPY_PATH}")
  exec_or_die('chmod', 'a+w', DB_TMP_PATH)
  exec_or_die('mv', DB_TMP_PATH, DB_PATH)
end

task :dbdump do
  table_names = `sqlite3 #{DB_PATH} .tables`.split(/\s+/)

  IGNORED_TABLES.each do |table_name| # Sanity check
    raise "#{table_name} does not exist in the DB #{DB_PATH}" unless table_names.include?(table_name)
  end

  backup_private_data

  table_names.each do |table_name|
    next if IGNORED_TABLES.include?(table_name)

    exec_or_die('sqlite3', DB_PATH, ".output #{DB_DUMP_DIR_PATH}/#{table_name}.sql", ".dump #{table_name}")
  end
end

task :dbcommit do
  Dir.chdir(DB_DUMP_DIR_PATH) do
    exec_or_die('git', 'reset', 'HEAD')
    exec_or_die('git', 'commit', '-m', 'Updated DB dump', '-p')
    exec_or_die('git', 'push')
    exec_or_die('git', 'show', '-q')
  end
end

#
# RELEASES MANAGEMENT
#

def do_release(branch, environment)
  exec_or_die('git', 'checkout', branch)
  exec_or_die('git', 'merge', '--no-edit', 'develop')
  exec_or_die('git', 'push')
  exec_or_die('git', 'checkout', 'develop')
  exec_or_die('git', 'merge', branch)
  exec_or_die('git', 'push')
  exec_or_die('ansible/update_environments.yaml', '--limit', environment)
end

task('release-beta') { do_release('beta', 'beta') }
task('release-prod') { do_release('main', 'prod') }

task(:composer_upgrade) { docker('composer', '--no-cache', 'upgrade') } # No cache in the container
task(:yarn_upgrade) { exec_or_die('yarn', 'upgrade') }
task(:yarn_encore_production) { exec_or_die('yarn', 'encore', 'production') }
task 'update-deps': %i[composer_upgrade yarn_upgrade yarn_encore_production phpunit]
task('commit-deps') { exec_or_die('git', 'commit', '-m', 'Updated 3rd party dependencies', 'composer.lock', 'symfony.lock', 'yarn.lock') }

task yep: [:yarn_encore_production]

#
# COMMISSIONS STATUS UPDATES
#

task 'get-snapshots' do
  exec_or_die('rsync', '--recursive', '--progress', '--human-readable', '--compress', '--checksum',
              'getfursu.it:/var/www/prod/var/snapshots/', 'var/snapshots/')
end

mtask(:cst, :console, 'app:tracker:run-updates', 'commissions')
mtask(:cstc, :cst, '--commit')
mtask(:cstr, :cst, '--refetch')

#
# IMPORT TASKS
#

task 'get-submissions' do
  exec_or_die('rsync', '--recursive', '--progress', '--human-readable', '--compress', '--checksum',
              'getfursu.it:/var/www/prod/var/iuFormData/', IMPORT_DIR_PATH)
  exec_or_die('aws', 's3', 'sync', '--size-only', IU_SUBMISSIONS_S3_PATH, IMPORT_DIR_PATH)
end

mtask(:import, :console, 'app:data:import', IMPORT_DIR_PATH, FIXES_FILE_PATH)
mtask(:importf, :import, '--fix-mode')
mtask(:importc, :import, '--commit')

#
# DATA TIDY TASKS
#

mtask(:tidy, :console, 'app:data:tidy', FIXES_FILE_PATH)
mtask(:tidyc, :tidy, '--commit')
