package web.snapshots

import web.url.Url
import java.io.FileNotFoundException

class SnapshotsManager(private val storeDirPath: String) {
    private val pathProvider = FileSystemPathProvider()

    fun get(url: Url, retrieve: (url: Url) -> Snapshot, refetch: Boolean): Snapshot {
        val snapshotDirPath = "$storeDirPath/" + pathProvider.getSnapshotDirPath(url)

        if (!refetch) {
            try {
                return Snapshot.loadFrom(snapshotDirPath)
            } catch (_: FileNotFoundException) {
                // OK
            }
        }

        val snapshot = retrieve(url)
        snapshot.saveTo(snapshotDirPath)

        return snapshot
    }
}
