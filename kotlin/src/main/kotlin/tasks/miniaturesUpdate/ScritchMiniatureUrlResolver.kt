package tasks.miniaturesUpdate

import data.JsonException
import data.JsonNavigator
import io.ktor.http.*
import web.client.CookieEagerHttpClient
import web.client.FastHttpClient
import web.client.GentleHttpClient
import web.client.HttpClientInterface
import web.snapshots.Snapshot
import web.url.FreeUrl
import web.url.Url

class ScritchMiniatureUrlResolver(
    httpClient: HttpClientInterface? = null,
) : MiniatureUrlResolver {
    private val graphQlUrl = FreeUrl("https://scritch.es/graphql")

    private val httpClient = httpClient ?: CookieEagerHttpClient(GentleHttpClient(FastHttpClient()))
    private val regex: Regex = Regex("^https://scritch\\.es/pictures/(?<pictureId>[-a-f0-9]{36})\$")

    override fun supports(url: String) = regex.containsMatchIn(url)

    override fun getMiniatureUrl(url: Url): String {
        val pictureId = regex.find(url.getUrl())?.groups?.get("pictureId")?.value
            ?: throw MiniatureUrlResolverException("Failed matching pictureId in the photo URL")

        val response = getResponseForPictureId(pictureId)

        if (response.metadata.httpCode != 200) {
            throw MiniatureUrlResolverException("Non-200 HTTP response code")
        }

        try {
            return JsonNavigator(response.contents).getNonEmptyString("data/medium/thumbnail")
        } catch (exception: JsonException) {
            throw MiniatureUrlResolverException("Wrong JSON data", exception)
        }
    }

    private fun getResponseForPictureId(pictureId: String): Snapshot {
        val csrfToken = getCsrfToken()
        val jsonPayload = getGraphQlJsonPayload(pictureId)

        val headers = mapOf(
            "Content-Type" to "application/json",
            "X-CSRF-Token" to csrfToken,
            "authorization" to "Scritcher $csrfToken",
        )

        return httpClient.fetch(graphQlUrl, HttpMethod.Post, headers, jsonPayload)
    }

    private fun getGraphQlJsonPayload(pictureId: String): String {
        return "{\"operationName\": \"Medium\", \"variables\": {\"id\": \"$pictureId\"}, \"query\": \"query " +
                "Medium(\$id: ID!, \$tagging: Boolean) { medium(id: \$id, tagging: \$tagging) { thumbnail } }\"}"
    }

    private fun getCsrfToken(): String = getOptionalCsrfToken() ?: getFirstRequiredCsrfToken()

    private fun getOptionalCsrfToken(): String? {
        return httpClient.getSingleCookieValue("https://scritch.es/", "csrf-token")
    }

    private fun getFirstRequiredCsrfToken(): String {
        httpClient.fetch(FreeUrl("https://scritch.es/"))

        return getOptionalCsrfToken() ?: throw MiniatureUrlResolverException("Missing csrf-token cookie")
    }
}
