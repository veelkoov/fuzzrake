package web.snapshots

import java.io.FileNotFoundException

class SnapshotsManager(private val storeDirPath: String) {
    private val pathProvider = FileSystemPathProvider()

    fun get(url: String): Snapshot {
        val snapshotDirPath = pathProvider.getSnapshotDirPath(url)

        return try {
            return Snapshot.loadFrom("$storeDirPath/$snapshotDirPath")
        } catch (_: FileNotFoundException) { // FIXME: This is not how it should be
            Snapshot("", SnapshotMetadata(url, "UNKNOWN", "", 0, mapOf(), 0, listOf()))
        }
    }
}
