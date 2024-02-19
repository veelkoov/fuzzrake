package web.snapshots

import digests.SHA224
import web.url.Url

class FileSystemPathProvider {
    private val urlPrefixAndSuffixRegex = Regex("^https?://(www\\.)?|[?#].+$", RegexOption.IGNORE_CASE)
    private val fsUnfriendlyCharactersRegex = Regex("[^a-z0-9_.-]+", RegexOption.IGNORE_CASE)

    fun getSnapshotDirPath(url: Url): String {
        val hostName = url.getHost().removePrefix("www.")

        val urlFsSafe = toFsSafeString(url.getUrl())
            .removePrefix(hostName)
            .trimStart('_')

        val firstLetter = "${hostName}_".uppercase()[0]
        val optionalDash = if ("" == urlFsSafe) "" else "-"
        val urlHash = SHA224.hexOf(url.getUrl())

        return "$firstLetter/$hostName/$urlFsSafe$optionalDash$urlHash"
    }

    private fun toFsSafeString(url: String): String {
        return url
            .replace(urlPrefixAndSuffixRegex, "")
            .replace(fsUnfriendlyCharactersRegex, "_")
            .trim('_')
    }
}
