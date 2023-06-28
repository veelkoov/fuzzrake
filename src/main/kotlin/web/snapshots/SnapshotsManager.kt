package web.snapshots

import web.url.Url
import java.io.FileNotFoundException

class SnapshotsManager(private val storeDirPath: String) {
    private val pathProvider = FileSystemPathProvider()

    fun get(url: Url, retrieve: (url: Url) -> Snapshot): Snapshot {
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
