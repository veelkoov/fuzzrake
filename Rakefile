task :default do
  system('rake', '--tasks', '--all')
end

task :sg do
  system('ansible/update_sg.yaml')
end

task :dbpush do
  system('ansible/update_remote_db.yaml')
end

task :dbpull do
  system('ansible/update_local_db.yaml')
end

task :dbdump do
  system('ansible/db_dump.yaml')
end

task :dbcommit do
  system('git', 'reset', 'HEAD')
  system('git', 'commit', '-m', 'Updated DB dump', '-p', 'db_dump/fuzzrake.sql')
end

task 'php-cs-fixer' do
  system('bin/php-cs-fixer')
end

task :phpunit do
  system('bin/phpunit')
end

task :qa => ['php-cs-fixer', :phpunit]
