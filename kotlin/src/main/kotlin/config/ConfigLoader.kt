package config

import data.Yaml
import java.io.File

private const val CFG_FILE_NAME = "fuzzrake-config.yaml"

class ConfigLoader {
    fun locateAndLoad(): Configuration {
        val configurationFile = searchConfigFilePath()
        val yamlConfiguration = Yaml.readFrom(configurationFile, YamlConfiguration::class.java)

        return Configuration.from(configurationFile.parentFile.toPath(), yamlConfiguration)
    }

    private fun searchConfigFilePath(): File {
        var candidatePath = File("").absoluteFile.canonicalFile

        while (true) {
            val candidateFile = candidatePath.resolve(CFG_FILE_NAME)

            if (candidateFile.isFile) {
                return candidateFile
            }

            val parentCandidatePath = candidatePath.parentFile

            if (parentCandidatePath != null) {
                candidatePath = parentCandidatePath
            } else {
                throw RuntimeException("Failed to locate the $CFG_FILE_NAME file")
            }
        }
    }
}
