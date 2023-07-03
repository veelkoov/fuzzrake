package web.url

class UrlForTracking(
    private val original: Url,
    private val url: String,
) : AbstractUrl(url, original.getStrategy()) {
    override fun getUrl() = url

    override fun recordSuccessfulFetch() = original.recordSuccessfulFetch()
    override fun recordFailedFetch(code: Int, reason: String) = original.recordFailedFetch(code, reason)
}
