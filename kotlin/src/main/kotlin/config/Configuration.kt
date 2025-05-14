package config

import java.nio.file.Path

data class Configuration(
    val databasePath: String,
    val snapshotsStoreDirPath: String,
) {
    companion object {
        fun from(path: Path, yamlConfiguration: YamlConfiguration): Configuration {
            return Configuration(
                path.resolve(yamlConfiguration.databasePath).toString(),
                path.resolve(yamlConfiguration.snapshotsStoreDirPath).toString(),
            )
        }
    }
}
