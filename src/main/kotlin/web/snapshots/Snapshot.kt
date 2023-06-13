package web.snapshots

import kotlinx.serialization.decodeFromString
import kotlinx.serialization.json.Json
import java.io.File

data class Snapshot(
    val contents: String,
    val metadata: SnapshotMetadata,
) {
    companion object {
        fun loadFrom(directoryPath: String): Snapshot {
            val contents = loadContents(directoryPath)
            val metadata = loadMetadata(directoryPath)

            return Snapshot(contents, metadata)
        }

        private fun loadContents(directoryPath: String): String {
            return File("$directoryPath/contents.data").readText()
        }

        private fun loadMetadata(directoryPath: String): SnapshotMetadata {
            val jsonString = File("$directoryPath/metadata.json").readText()

            return Json.decodeFromString<SnapshotMetadata>(patchPhpJsonArrays(jsonString))
        }

        private fun patchPhpJsonArrays(json: String): String { // TODO: Eliminate
            return json.replace(
                ",\"headers\":[],",
                ",\"headers\":{},",
            )
        }
    }
}
