package tasks.miniaturesUpdate

import data.JsonException
import data.JsonNavigator
import web.client.CookieEagerHttpClient
import web.client.FastHttpClient
import web.client.GentleHttpClient
import web.client.HttpClientInterface
import web.snapshots.Snapshot
import web.url.Url

abstract class JsonResponseBasedMiniatureUrlResolver(
    httpClient: HttpClientInterface?,
    pattern: String,
) : MiniatureUrlResolver {
    protected val httpClient = httpClient ?: CookieEagerHttpClient(GentleHttpClient(FastHttpClient()))
    protected val regex: Regex = Regex(pattern)

    override fun supports(url: String) = regex.containsMatchIn(url)

    override fun getMiniatureUrl(url: Url): String {
        val pictureId = getPictureId(url)
        val response = getResponseForPictureId(pictureId)

        if (response.metadata.httpCode != 200) {
            throw MiniatureUrlResolverException("Non-200 HTTP response code")
        }

        try {
            return miniatureUrlFromJsonData(JsonNavigator(response.contents))
        } catch (exception: JsonException) {
            throw MiniatureUrlResolverException("Wrong JSON data", exception)
        }
    }

    abstract fun getResponseForPictureId(pictureId: String): Snapshot

    @Throws(JsonException::class)
    protected abstract fun miniatureUrlFromJsonData(data: JsonNavigator): String

    private fun getPictureId(photoUrl: Url): String
    {
        return regex.find(photoUrl.getUrl())?.groups?.get("pictureId")?.value
            ?: throw MiniatureUrlResolverException("Failed matching pictureId in the photo URL")
    }
}
