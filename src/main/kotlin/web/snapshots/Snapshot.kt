package web.snapshots

import kotlinx.serialization.decodeFromString
import kotlinx.serialization.encodeToString
import kotlinx.serialization.json.Json
import java.io.File

data class Snapshot(
    val contents: String,
    val metadata: SnapshotMetadata,
) {
    fun saveTo(snapshotDirPath: String) {
        File(snapshotDirPath).mkdirs()

        File(contentsPath(snapshotDirPath)).writeText(contents)
        File(metadataPath(snapshotDirPath)).writeText(Json.encodeToString(metadata))
    }

    companion object {
        fun loadFrom(snapshotDirPath: String): Snapshot {
            val contents = loadContents(snapshotDirPath)
            val metadata = loadMetadata(snapshotDirPath)

            return Snapshot(contents, metadata)
        }

        private fun loadContents(snapshotDirPath: String): String {
            return File(contentsPath(snapshotDirPath)).readText()
        }

        private fun loadMetadata(snapshotDirPath: String): SnapshotMetadata {
            val jsonString = File(metadataPath(snapshotDirPath)).readText()

            return Json.decodeFromString<SnapshotMetadata>(jsonString)
        }

        private fun metadataPath(snapshotDirPath: String) = "$snapshotDirPath/metadata.json"
        private fun contentsPath(snapshotDirPath: String) = "$snapshotDirPath/contents.data"
    }
}
