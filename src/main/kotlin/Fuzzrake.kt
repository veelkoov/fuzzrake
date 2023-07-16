import com.github.ajalt.clikt.core.CliktCommand
import com.github.ajalt.clikt.core.subcommands
import com.github.ajalt.clikt.parameters.options.flag
import com.github.ajalt.clikt.parameters.options.option
import config.ConfigLoader
import tracking.Tracker
import tracking.TrackerOptions

class FuzzrakeCmd : CliktCommand(name="fuzzrake") {
    override fun run() = Unit
}

class TrackerCmd: CliktCommand(name="tracker", help="Run commissions tracking") {
    private val refetch by option("--refetch", "-r").flag(default = false)

    override fun run() {
        val config = ConfigLoader()
            .locateAndLoad()
        val options = TrackerOptions(refetch)

        val tracker = Tracker(config, options)

        tracker.run()
    }
}

fun main(args: Array<String>) = FuzzrakeCmd()
    .subcommands(TrackerCmd())
    .main(args)
