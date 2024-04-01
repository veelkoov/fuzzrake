package tasks.miniaturesUpdate

import io.ktor.http.*
import web.client.CookieEagerHttpClient
import web.client.FastHttpClient
import web.client.GentleHttpClient
import web.client.HttpClientInterface
import web.url.FreeUrl
import web.url.Url

class FurtrackMiniatureUrlResolver(
    httpClient: HttpClientInterface? = null,
) : MiniatureUrlResolver {
    private val httpClient = httpClient ?: CookieEagerHttpClient(GentleHttpClient(FastHttpClient()))
    private val regex: Regex = Regex("^https://www.furtrack.com/p/(?<pictureId>\\d+)\$")

    override fun supports(url: String) = regex.containsMatchIn(url)

    override fun getMiniatureUrl(url: Url): String {
        val pictureId = regex.find(url.getUrl())?.groups?.get("pictureId")?.value
            ?: throw MiniatureUrlResolverException("Failed matching pictureId in the photo URL")

        val pictureUri = "https://orca2.furtrack.com/thumb/$pictureId.jpg"

        val response = httpClient.fetch(FreeUrl(pictureUri), HttpMethod.Head)

        if (200 != response.metadata.httpCode) {
            throw MiniatureUrlResolverException("Non-200 HTTP response code")
        }

        return pictureUri
    }
}
