package web.client

import time.UTC
import java.net.URI

private const val DELAY_FOR_HOST_SEC = 10

class HostCallsQueue {
    /**
     * Key = hostname. Value = Next allowed call timestamp (epoch sec).
     */
    private val hostnameToTime: MutableMap<String, Long> = mutableMapOf()

    fun <T> patiently(url: String, call: () -> T): T {
        val hostname = URI(url).host // FIXME: This should not be done here

        waitUtilCallAllowed(hostname)

        val result = call()

        hostnameToTime[hostname] = UTC.Now.epochSec() + DELAY_FOR_HOST_SEC

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
