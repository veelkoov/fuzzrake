package web.client.utils

import time.UTC
import web.url.Url
import java.util.concurrent.ConcurrentHashMap

private const val DELAY_FOR_HOST_SEC = 10

class HostCallsQueue {
    /**
     * Key = hostname. Value = Next allowed call timestamp (epoch sec).
     */
    private val hostnameToTime = ConcurrentHashMap<String, Long>()

    fun <T> patiently(url: Url, call: () -> T): T {
        waitUtilCallAllowed(url.getHost())

        val result = call()

        hostnameToTime[url.getHost()] = UTC.Now.epochSec() + DELAY_FOR_HOST_SEC

        return result
    }

    private fun waitUtilCallAllowed(hostname: String) {
        val waitUntilEpochSec = hostnameToTime.computeIfAbsent(hostname) { UTC.Now.epochSec() }

        val secondsToWait = waitUntilEpochSec - UTC.Now.epochSec()

        if (secondsToWait > 0) {
            Thread.sleep(secondsToWait * 1000)
        }
    }
}
