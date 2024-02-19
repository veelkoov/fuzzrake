package web.snapshots

import org.junit.jupiter.api.DynamicTest.dynamicTest
import org.junit.jupiter.api.TestFactory
import web.url.FreeUrl
import kotlin.test.assertTrue

class FileSystemPathProviderTest {
    private val subject = FileSystemPathProvider()

    @TestFactory
    fun getSnapshotDirPath() = mapOf(
        "https://www.tumblr.com/getfursuit" to "T/tumblr.com/getfursuit",
        "https://furries.club/@getfursuit" to "F/furries.club/getfursuit",
        "https://github.com/veelkoov/fuzzrake/pull/194" to "G/github.com/veelkoov_fuzzrake_pull_194",
        "https://getfursu.it/data_updates.html#anchor" to "G/getfursu.it/data_updates.html",
    ).map { (url, expectedPrefix) ->
        dynamicTest(url) {
            val result = subject.getSnapshotDirPath(FreeUrl(url))

            assertTrue(result.startsWith(expectedPrefix), "Unexpected prefix: $result")
        }
    }
}
