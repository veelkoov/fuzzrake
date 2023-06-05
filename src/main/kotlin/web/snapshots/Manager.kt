package web.snapshots

import digests.SHA224
import kotlinx.serialization.decodeFromString
import kotlinx.serialization.json.Json
import tracking.contents.JsonSnapshot
import tracking.contents.Snapshot
import java.io.File
import java.io.FileNotFoundException
import java.net.URL

object Manager {
    private const val basePath = "/home/fuzzrake/var/snapshots"

    fun get(url: String): Snapshot {
        val baseDir = getBaseDir(url)
        return try {
            val jsonString = File("$baseDir/metadata.json").readText().replace(",\"headers\":[],", ",\"headers\":{},")
            val jsonData = Json.decodeFromString<JsonSnapshot>(jsonString)
            val contents = File("$baseDir/contents.data").readText()

            Snapshot(contents, jsonData.url)
        } catch (_: FileNotFoundException) { // FIXME: This is not how it should be
            Snapshot("", "")
        }
    }

    private fun getBaseDir(url: String): String
    {
        val hostName = URL(url).host.removePrefix("www.")

        val urlFsSafe = safeFileNameFromUrl(url)
            .removePrefix(hostName)
            .trimStart('_')

        val firstLetter = "${hostName}_".uppercase()[0]
        val optionalDash = if ("" == urlFsSafe) "" else "-"
        val urlHash = SHA224.hexOf(url)

        return "$basePath/$firstLetter/$hostName/$urlFsSafe$optionalDash$urlHash"
    }

    private fun safeFileNameFromUrl(url: String): String {
        return url
            .replace(Regex("^https?://(www\\.)?|[?#].+$", RegexOption.IGNORE_CASE), "")
            .replace(Regex("[^a-z0-9_.-]+", RegexOption.IGNORE_CASE), "_")
            .trim('_')
    }
}
