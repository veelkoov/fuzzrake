package testUtils

import io.ktor.client.*
import io.ktor.client.engine.mock.*
import io.ktor.http.*
import io.ktor.http.content.*
import io.ktor.utils.io.*
import web.client.FastHttpClient
import kotlin.test.assertEquals
import kotlin.test.assertIs

data class ExpectedHttpCall(
    val url: String,
    val requestJsonContent: String?,
    val requestHeaders: Map<String, String>,
    val responseContent: String,
    val responseHeaders: Map<String, String>,
    val responseStatus: HttpStatusCode = HttpStatusCode.OK,
)

fun getHttpClientMock(vararg expectedHttpCalls: ExpectedHttpCall) =
    getHttpClientMock(expectedHttpCalls.toMutableList())

fun getHttpClientMock(expectedHttpCalls: List<ExpectedHttpCall>): FastHttpClient {
    val remainingHttpCalls = expectedHttpCalls.toMutableList()

    val mockEngine = MockEngine {
        val httpCall = remainingHttpCalls.removeFirst()

        assertEquals(httpCall.url, it.url.toString(), "Wrong call order")

        if (httpCall.requestJsonContent != null) {
            // Not pretty sure if testing the "application/json" header properly here
            assertIs<TextContent>(it.body)
            assertEquals(
                httpCall.requestJsonContent, (it.body as TextContent).text,
                "Wrong request payload"
            )
            assertEquals(
                "application/json", it.body.contentType.toString(),
                "Wrong request payload type"
            )
        }

        httpCall.requestHeaders.forEach { (header, value) ->
            assertEquals(
                it.headers.getAll(header), listOf(value),
                "Wrong request headers for '${httpCall.url}' call"
            )
        }

        respond(
            content = ByteReadChannel(httpCall.responseContent),
            headers = HeadersImpl(httpCall.responseHeaders.map { (header, value) ->
                header to listOf(value)
            }.toMap()),
            status = httpCall.responseStatus,
        )
    }

    return FastHttpClient(HttpClient(mockEngine, FastHttpClient.getConfig(retries = false)))
}
