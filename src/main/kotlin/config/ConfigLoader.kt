package config

import data.Yaml
import java.io.File

private const val CFG_FILE_NAME = "fuzzrake-config.yaml"

class ConfigLoader {
    fun locateAndLoad(): Configuration {
        val file = searchConfigFilePath()

        return Yaml.readFrom(file, Configuration::class.java)
    }

    private fun searchConfigFilePath(): File {
        var candidatePath = File("build/classes").absoluteFile.canonicalFile

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
