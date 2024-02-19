package web.client

import io.ktor.http.*
import web.snapshots.Snapshot
import web.url.Url

interface HttpClientInterface {
    fun fetch(
        url: Url,
        method: HttpMethod = HttpMethod.Get,
        addHeaders: Map<String, String> = mapOf(),
        payload: String? = null,
    ): Snapshot

    fun getSingleCookieValue(url: String, cookieName: String): String?
}
