package config

import com.fasterxml.jackson.annotation.JsonProperty

data class Configuration(
    @JsonProperty("database_path")
    val databasePath: String,

    @JsonProperty("snapshots_store_dir_path")
    val snapshotsStoreDirPath: String,
)
