package web.snapshots

import testUtils.disposableDirectory
import testUtils.getSnapshot
import time.UTC
import kotlin.test.Test
import kotlin.test.assertEquals
import kotlin.test.assertNotSame

class SnapshotTest {
    @Test
    fun `Saving and loading works`() {
        val expected = getSnapshot(
            "test contents",
            "test URL",
            "test owner name",
            UTC.Now.dateTime().toString(),
            200,
            mapOf(
                "content-type" to listOf("text/plain"),
                "encoding" to listOf(),
            ),
            listOf("test error"),
        )

        val actual = disposableDirectory { tempDirectoryPath ->
            expected.saveTo(tempDirectoryPath.toString())

            Snapshot.loadFrom(tempDirectoryPath.toString())
        }

        assertNotSame(expected, actual)
        assertEquals(expected, actual)
    }
}
