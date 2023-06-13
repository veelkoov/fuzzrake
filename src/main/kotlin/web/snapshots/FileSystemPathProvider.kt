package web.snapshots

import digests.SHA224
import java.net.URL

class FileSystemPathProvider {
    private val urlPrefixAndSuffixRegex = Regex("^https?://(www\\.)?|[?#].+$", RegexOption.IGNORE_CASE)
    private val fsUnfriendlyCharactersRegex = Regex("[^a-z0-9_.-]+", RegexOption.IGNORE_CASE)

    fun getSnapshotDirPath(url: String): String {
        val hostName = URL(url).host.removePrefix("www.")

        val urlFsSafe = toFsSafeString(url)
            .removePrefix(hostName)
            .trimStart('_')

        val firstLetter = "${hostName}_".uppercase()[0]
        val optionalDash = if ("" == urlFsSafe) "" else "-"
        val urlHash = SHA224.hexOf(url)

        return "$firstLetter/$hostName/$urlFsSafe$optionalDash$urlHash"
    }

    private fun toFsSafeString(url: String): String {
        return url
            .replace(urlPrefixAndSuffixRegex, "")
            .replace(fsUnfriendlyCharactersRegex, "_")
            .trim('_')
    }
}
