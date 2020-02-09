# frozen_string_literal: true

IMPORT_FILE_PATH = 'imports/IU form v5 - getfursu.it.csv.zip'
FIXES_FILE_PATH = 'imports/import-fixes-v5.txt'

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

def do_release(branch)
  exec_or_die('git', 'checkout', branch)
  exec_or_die('git', 'merge', 'develop')
  exec_or_die('git', 'push')
  exec_or_die('git', 'checkout', 'develop')
end

task :default do
  exec_or_die('rake', '--tasks', '--all')
end

task :sg do
  exec_or_die('ansible/update_sg.yaml')
end

task :dbpush do
  exec_or_die('ansible/update_remote_db.yaml')
end

task :dbpull do
  exec_or_die('ansible/update_local_db.yaml')
end

task :dbdump do
  db_path = 'var/db.sqlite'
  out_tmp_path = 'db_dump/fuzzrake.tmp.sql'
  out_path = 'db_dump/fuzzrake.sql'
  out_prv_path = 'db_dump/fuzzrake-private.nocommit.sql'

  exec_or_die('sqlite3', db_path, ".output #{out_tmp_path}", '.dump')
  exec_or_die('bin/format_dump.py', out_tmp_path, out_path, out_prv_path)
  exec_or_die('rm', out_tmp_path)
end

task :dbcommit do
  exec_or_die('git', 'reset', 'HEAD')
  exec_or_die('git', 'commit', '-m', 'Updated DB dump',
              '-p', 'db_dump/fuzzrake.sql')
end

task 'php-cs-fixer' do
  docker('vendor/bin/php-cs-fixer', 'fix')
end

task :phpunit do
  docker('bin/phpunit')
end

task 'get-snapshots' do
  exec_or_die('rsync', '--recursive', '--progress', '--human-readable',
              'getfursu.it:/var/www/prod/var/snapshots/', 'var/snapshots/')
end

task :import do
  symfony_console('app:data:import', IMPORT_FILE_PATH, FIXES_FILE_PATH)
end

task :importf do
  symfony_console('app:data:import', IMPORT_FILE_PATH, FIXES_FILE_PATH,
                  '--fix-mode')
end

task :importc do
  symfony_console('app:data:import', IMPORT_FILE_PATH, FIXES_FILE_PATH,
                  '--commit')
end

task 'release-beta' do
  do_release('beta')
end

task 'release-prod' do
  do_release('master')
end

task qa: ['php-cs-fixer', :phpunit]

task :cc do
  symfony_console('cache:clear')
end
