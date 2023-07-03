package web.url

import tracking.website.Strategy

interface Url {
    fun getUrl(): String
    fun getStrategy(): Strategy
    fun getHost(): String
    fun recordSuccessfulFetch()
    fun recordFailedFetch(code: Int, reason: String)
}
