import com.github.ajalt.clikt.core.CliktCommand
import com.github.ajalt.clikt.core.subcommands
import com.github.ajalt.clikt.parameters.options.default
import com.github.ajalt.clikt.parameters.options.flag
import com.github.ajalt.clikt.parameters.options.option
import com.github.ajalt.clikt.parameters.types.int
import com.github.ajalt.clikt.parameters.types.restrictTo
import config.ConfigLoader
import tracking.Tracker
import tracking.TrackerOptions
import web.UrlInspector
import web.UrlInspectorOptions

class FuzzrakeCmd : CliktCommand(name="fuzzrake") {
    override fun run() = Unit
}

class TrackerCmd : CliktCommand(
    name = "tracker",
    help = "Run commissions tracking",
) {
    private val refetch by option("--refetch", "-r").flag(default = false)

    override fun run() {
        val config = ConfigLoader().locateAndLoad()
        val options = TrackerOptions(refetch)
        val tracker = Tracker(config, options)

        tracker.run()
    }
}

class UrlInspectorCmd : CliktCommand(
    name = "inspect-urls",
    help = "Fetch URLs starting from the oldest to refresh their last success/failure status",
) {
    private val limit by option("--limit", "-l").int().restrictTo(1).default(2)

    override fun run() {
        val config = ConfigLoader().locateAndLoad()
        val options = UrlInspectorOptions(limit)
        val inspector = UrlInspector(config, options)

        inspector.run()
    }
}

fun main(args: Array<String>) = FuzzrakeCmd()
    .subcommands(
        TrackerCmd(),
        UrlInspectorCmd(),
    )
    .main(args)
