package web.url

class UrlForTracking(
    private val original: Url,
    url: String,
) : AbstractUrl(url, original.getStrategy()) {
    override fun getOriginalUrl() = original.getOriginalUrl()
    override fun recordSuccessfulFetch() = original.recordSuccessfulFetch()
    override fun recordFailedFetch(code: Int, reason: String) = original.recordFailedFetch(code, reason)
}
