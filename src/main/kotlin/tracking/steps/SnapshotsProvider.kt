package tracking.steps

import kotlinx.serialization.decodeFromString
import kotlinx.serialization.json.Json
import tracking.contents.JsonSnapshot
import tracking.contents.Snapshot
import tracking.creator.Creator
import tracking.creator.CreatorItems
import java.io.File
import java.util.stream.Stream
import kotlin.streams.asStream

class SnapshotsProvider { // FIXME: All of it
    fun getSnapshotsStream(): Stream<CreatorItems<Snapshot>> {
        val inputDir = File("/home/fuzzrake/var/snapshots")

        if (!inputDir.isDirectory) {
            throw IllegalArgumentException("${inputDir.absolutePath} is not a valid directory")
        }

        return inputDir.walk()
            .asStream().parallel()
            .filter { it.isFile && "metadata.json" == it.name }
            .map { it.absolutePath }
            .sorted()
            .map { filePath ->
                val jsonString = File(filePath).readText().replace(",\"headers\":[],", ",\"headers\":{},")
                val jsonData = Json.decodeFromString<JsonSnapshot>(jsonString)
                val contents = File(filePath.removeSuffix("metadata.json") + "contents.data").readText()

                val creator = Creator(listOf(jsonData.ownerName))
                val snapshot = Snapshot(contents, jsonData.url)

                CreatorItems(creator, listOf(snapshot))
            }
    }
}
