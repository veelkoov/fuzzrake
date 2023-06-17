package web.snapshots

import java.io.FileNotFoundException

class SnapshotsManager(private val storeDirPath: String) {
    private val pathProvider = FileSystemPathProvider()

    fun get(url: String, retrieve: (url: String) -> Snapshot): Snapshot {
        val snapshotDirPath = "$storeDirPath/" + pathProvider.getSnapshotDirPath(url)

        return try {
            return Snapshot.loadFrom(snapshotDirPath)
        } catch (_: FileNotFoundException) {
            val snapshot = retrieve(url)

            snapshot.saveTo(snapshotDirPath)

            snapshot
        }
    }
}
