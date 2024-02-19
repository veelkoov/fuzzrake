package tasks.miniaturesUpdate

import web.url.Url

interface MiniatureUrlResolver {
    fun supports(url: String): Boolean

    @Throws(MiniatureUrlResolverException::class)
    fun getMiniatureUrl(url: Url): String
}
