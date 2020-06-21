# frozen_string_literal: true

IMPORT_FILE_PATH = 'imports/iu_form_current.csv.zip'
FIXES_FILE_PATH = 'imports/import-fixes-v5.txt'

DB_PATH = 'var/db.sqlite'
DB_TMP_PATH = DB_PATH + '.tmp'

DB_DUMP_DIR_PATH = 'db_dump'
DB_DUMP_TMP_PATH = DB_DUMP_DIR_PATH + '/fuzzrake.tmp.sql'
DB_DUMP_PRV_COPY_PATH = DB_DUMP_DIR_PATH + '/artisans_private_data-' \
                      + Time.now.getutc.strftime('%Y-%m-%d_%H-%M-%S') + '.sql'

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
  print("Executing: '" + args.join("' '") + "'\n")
  system(*args) || raise('Command returned non-zero exit code')
end

def docker(*args)
  exec_or_die('docker', 'exec', '-ti', 'fuzzrake', *args)
end

task(:console) { |_t, args| docker('./bin/console', *args) }

#
# MISCELLANEOUS TASKS
#

task(:default)       { exec_or_die('rake', '--tasks', '--all') }
task(:sg)            { exec_or_die('ansible/update_sg.yaml') }
task('php-cs-fixer') { docker('./vendor/bin/php-cs-fixer', 'fix') }
task(:phpunit)       { docker('./bin/phpunit') }
task qa: ['php-cs-fixer', :phpunit]
mtask(:cc, :console, 'cache:clear')

#
# DATABASE MANAGEMENT
#

task :dbpush do
  exec_or_die('cp', DB_PATH, DB_TMP_PATH)

  exec_or_die('sqlite3', DB_TMP_PATH, 'DELETE FROM artisans_private_data;')

  exec_or_die('scp', '-p', DB_TMP_PATH, 'getfursu.it:/var/www/prod/' + DB_PATH)
  exec_or_die('scp', '-p', DB_TMP_PATH, 'getfursu.it:/var/www/beta/' + DB_PATH)

  exec_or_die('rm', DB_TMP_PATH)
end

task :dbpull do
  exec_or_die('scp', '-p', 'getfursu.it:/var/www/prod/' + DB_PATH, DB_TMP_PATH)
  exec_or_die('sqlite3', DB_PATH, ".output #{DB_DUMP_PRV_COPY_PATH}", '.dump artisans_private_data')
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
task('release-prod') { do_release('master', 'prod') }

#
# COMMISSIONS STATUS UPDATES
#

task 'get-snapshots' do
  exec_or_die('rsync', '--recursive', '--progress', '--human-readable',
              'getfursu.it:/var/www/prod/var/snapshots/', 'var/snapshots/')
end

mtask(:cst, :console, 'app:update:commissions')
mtask(:cstc, :cst, '--commit')
mtask(:cstr, :cst, '--refetch')

#
# IMPORT TASKS
#

mtask(:import, :console, 'app:data:import', IMPORT_FILE_PATH, FIXES_FILE_PATH)
mtask(:importf, :import, '--fix-mode')
mtask(:importc, :import, '--commit')

#
# DATA TIDY TASKS
#

mtask(:tidy, :console, 'app:data:tidy', FIXES_FILE_PATH)
mtask(:tidyc, :tidy, '--commit')
