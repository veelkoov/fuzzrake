package tasks.miniaturesUpdate

import data.JsonNavigator
import io.ktor.http.*
import web.client.HttpClientInterface
import web.snapshots.Snapshot
import web.url.FreeUrl

class ScritchMiniatureUrlResolver(
    httpClient: HttpClientInterface? = null,
) : JsonResponseBasedMiniatureUrlResolver(
    httpClient,
    "^https://scritch\\.es/pictures/(?<pictureId>[-a-f0-9]{36})\$",
) {
    private val graphQlUrl = FreeUrl("https://scritch.es/graphql")

    override fun getResponseForPictureId(pictureId: String): Snapshot {
        val csrfToken = getCsrfToken()
        val jsonPayload = getGraphQlJsonPayload(pictureId)

        val headers = mapOf(
            "Content-Type" to "application/json",
            "X-CSRF-Token" to csrfToken,
            "authorization" to "Scritcher $csrfToken",
        )

        return httpClient.fetch(graphQlUrl, HttpMethod.Post, headers, jsonPayload)
    }

    override fun miniatureUrlFromJsonData(data: JsonNavigator): String {
        return data.getNonEmptyString("data/medium/thumbnail")
    }

    private fun getGraphQlJsonPayload(pictureId: String): String
    {
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
