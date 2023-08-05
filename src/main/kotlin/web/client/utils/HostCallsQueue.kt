package web.client.utils

import io.github.oshai.kotlinlogging.KotlinLogging
import time.UTC
import web.url.Url
import java.util.concurrent.ConcurrentHashMap

private const val DELAY_FOR_HOST_SEC = 5

private val logger = KotlinLogging.logger {}

class HostCallsQueue {
    /**
     * Key = hostname. Value = Next allowed call timestamp (epoch sec).
     */
    private val hostnameToTime = ConcurrentHashMap<String, Long>()

    fun <T> patiently(url: Url, call: () -> T): T {
        waitUtilCallAllowed(url)

        val result = call()

        hostnameToTime[url.getHost()] = UTC.Now.epochSec() + DELAY_FOR_HOST_SEC

        return result
    }

    private fun waitUtilCallAllowed(url: Url) {
        val waitUntilEpochSec = hostnameToTime.computeIfAbsent(url.getHost()) { UTC.Now.epochSec() }

        val secondsToWait = waitUntilEpochSec - UTC.Now.epochSec()

        if (secondsToWait > 0) {
            logger.info("Pausing for $secondsToWait seconds for ${url.getUrl()}")

            Thread.sleep(secondsToWait * 1000)
        }
    }
}
