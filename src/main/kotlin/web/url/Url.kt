package web.url

import tracking.website.Strategy

interface Url {
    fun getUrl(): String
    fun getStrategy(): Strategy
    fun getHost(): String
}
