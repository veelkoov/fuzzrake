# frozen_string_literal: true

IMPORT_FILE_PATH = 'imports/IU form v5 - getfursu.it.csv.zip'
FIXES_FILE_PATH = 'imports/import-fixes-v5.txt'

DB_PATH = 'var/db.sqlite'
DB_TMP_PATH = DB_PATH + '.tmp'

DB_DUMP_TMP_PATH = 'db_dump/fuzzrake.tmp.sql'
DB_DUMP_PATH = 'db_dump/fuzzrake.sql'
DB_DUMP_PRV_PATH = 'db_dump/fuzzrake-private.nocommit.sql'
DB_DUMP_PRV_COPY_PATH = 'db_dump/fuzzrake-private-' + Time.now.getutc.strftime('%Y-%m-%d_%H-%M-%S') + '.nocommit.sql'

#
# HELPER FUNCTIONS
#

def exec_or_die(*args)
  print("Executing: '" + args.join("' '") + "'\n")
  system(*args) || raise('Command returned non-zero exit code')
end

def docker(*args)
  # FIXME: container name hardcoded
  exec_or_die('docker', 'exec', '-ti', 'fuzzrake', *args)
end

def symfony_console(*args)
  docker('bin/console', *args)
end

#
# MISCELLANEOUS TASKS
#

task(:default) { exec_or_die('rake', '--tasks', '--all') }
task(:sg)      { exec_or_die('ansible/update_sg.yaml') }
task(:cc)      { symfony_console('cache:clear') }

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
  exec_or_die('cp', DB_DUMP_PRV_PATH, DB_DUMP_PRV_COPY_PATH)
  exec_or_die('sqlite3', DB_PATH, ".output #{DB_DUMP_TMP_PATH}", '.dump')
  exec_or_die('bin/format_dump.py', DB_DUMP_TMP_PATH, DB_DUMP_PATH, DB_DUMP_PRV_PATH)
  exec_or_die('rm', DB_DUMP_TMP_PATH)
end

task :dbcommit do
  exec_or_die('git', 'reset', 'HEAD')
  exec_or_die('git', 'commit', '-m', 'Updated DB dump', '-p', 'db_dump/fuzzrake.sql')
end

task('php-cs-fixer') { docker('vendor/bin/php-cs-fixer', 'fix') }
task(:phpunit)       { docker('bin/phpunit') }
task qa: ['php-cs-fixer', :phpunit]

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

def sc_cst(*args)
  symfony_console('app:update:commissions', *args)
end

task 'get-snapshots' do
  exec_or_die('rsync', '--recursive', '--progress', '--human-readable',
              'getfursu.it:/var/www/prod/var/snapshots/', 'var/snapshots/')
end

task(:cst)  { sc_cst }
task(:cstc) { sc_cst('--commit') }
task(:cstr) { sc_cst('--refetch') }

#
# IMPORT TASKS
#

def sc_data_import(*args)
  symfony_console('app:data:import', IMPORT_FILE_PATH, FIXES_FILE_PATH, *args)
end

task(:import)  { sc_data_import }
task(:importf) { sc_data_import('--fix-mode') }
task(:importc) { sc_data_import('--commit') }

#
# DATA TIDY TASKS
#

def sc_data_tidy(*args)
  symfony_console('app:data:tidy', FIXES_FILE_PATH, *args)
end

task(:tidy)  { sc_data_tidy }
task(:tidyc) { sc_data_tidy('--commit') }
