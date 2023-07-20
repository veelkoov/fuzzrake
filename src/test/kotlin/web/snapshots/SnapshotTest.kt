package web.snapshots

import time.UTC
import kotlin.io.path.createTempDirectory
import kotlin.test.AfterTest
import kotlin.test.Test
import kotlin.test.assertEquals
import kotlin.test.assertNotSame

class SnapshotTest {
    private var testSnapshotPath = createTempDirectory()

    @AfterTest
    fun removeTestDirectory() {
        if (!testSnapshotPath.toFile().deleteRecursively()) {
            throw RuntimeException("Failed to cleanup '$testSnapshotPath'")
        }
    }

    @Test
    fun `Saving and loading works`() {
        val expected = Snapshot(
            "test contents",
            SnapshotMetadata(
                "test URL",
                "test owner name",
                UTC.Now.dateTime().toString(),
                200,
                mapOf(
                    "content-type" to listOf("text/plain"),
                    "encoding" to listOf(),
                ),
                0,
                listOf("test error")
            ),
        )

        expected.saveTo(testSnapshotPath.toString())

        val actual = Snapshot.loadFrom(testSnapshotPath.toString())

        assertNotSame(expected, actual)
        assertEquals(expected, actual)
    }
}
