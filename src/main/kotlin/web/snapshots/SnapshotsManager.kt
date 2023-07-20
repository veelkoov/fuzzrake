package web.snapshots

import web.client.CookieEagerHttpClient
import web.client.FastHttpClient
import web.client.GentleHttpClient
import web.client.HttpClientInterface
import web.url.Url
import java.io.FileNotFoundException

class SnapshotsManager(
    private val storeDirPath: String,
    private val httpClient: HttpClientInterface = CookieEagerHttpClient(GentleHttpClient(FastHttpClient()))
) {
    private val pathProvider = FileSystemPathProvider()

    fun get(url: Url, refetch: Boolean): Snapshot {
        val snapshotDirPath = "$storeDirPath/" + pathProvider.getSnapshotDirPath(url)

        if (!refetch) {
            try {
                return Snapshot.loadFrom(snapshotDirPath)
            } catch (_: FileNotFoundException) {
                // OK
            }
        }

        val snapshot = httpClient.get(url)
        snapshot.saveTo(snapshotDirPath)

        return snapshot
    }
}
