# frozen_string_literal: true

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

def run_shell(*args)
  print("Executing: '#{args.join("' '")}'\n")
  system(*args) || raise('Command returned non-zero exit code')
end

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
