def exec_or_die(*args)
  system(*args) or raise "Command returned non-zero exit code"
end

def docker(*args) # FIXME: container name hardcoded
  exec_or_die('docker', 'exec', '-ti', 'fuzzrake', *args)
end

def console(*args)
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
  exec_or_die('ansible/db_dump.yaml')
end

task :dbcommit do
  exec_or_die('git', 'reset', 'HEAD')
  exec_or_die('git', 'commit', '-m', 'Updated DB dump', '-p', 'db_dump/fuzzrake.sql')
end

task 'php-cs-fixer' do
  docker('vendor/bin/php-cs-fixer', 'fix')
end

task :phpunit do
  docker('bin/phpunit')
end

task 'get-snapshots' do
  exec_or_die('rsync', '--recursive', '--progress', '--human-readable', 'getfursu.it:/var/www/prod/var/snapshots/', 'var/snapshots/')
end

task :import do
  console('app:data:import', 'imports/IU form v5 - getfursu.it.csv.zip', 'imports/import-fixes-v5.txt')
end

task :importf do
  console('app:data:import', 'imports/IU form v5 - getfursu.it.csv.zip', 'imports/import-fixes-v5.txt', '--fix-mode')
end

task :importc do
  console('app:data:import', 'imports/IU form v5 - getfursu.it.csv.zip', 'imports/import-fixes-v5.txt', '--commit')
end

task 'release-beta' do
  do_release('beta')
end

task 'release-prod' do
  do_release('master')
end

task :qa => ['php-cs-fixer', :phpunit]

task :cc do
  console('cache:clear')
end
