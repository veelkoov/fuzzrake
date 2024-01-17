import com.github.ajalt.clikt.core.CliktCommand
import com.github.ajalt.clikt.core.subcommands
import com.github.ajalt.clikt.parameters.options.default
import com.github.ajalt.clikt.parameters.options.flag
import com.github.ajalt.clikt.parameters.options.option
import com.github.ajalt.clikt.parameters.types.int
import com.github.ajalt.clikt.parameters.types.restrictTo
import config.ConfigLoader
import tasks.*
import tracking.Tracker
import tracking.TrackerOptions

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

class UrlInspectionCmd : CliktCommand(
    name = "inspect-urls",
    help = "Fetch URLs starting from the oldest to refresh their last success/failure status",
) {
    private val limit by option("--limit", "-l").int().restrictTo(1).default(2)

    override fun run() {
        val config = ConfigLoader().locateAndLoad()
        val options = UrlsInspectionOptions(limit)
        val inspector = UrlsInspection(config, options)

        inspector.run()
    }
}

class MiniaturesUpdateCmd : CliktCommand(
    name = "update-miniatures",
    help = "Update miniatures URLs based on changes in images URLs",
) {
    override fun run() {
        val config = ConfigLoader().locateAndLoad()
        val updater = MiniaturesUpdate(config)

        updater.execute()
    }
}

class SpeciesSyncCmd : CliktCommand(
    name = "sync-species",
    help = "Update species / creator species in the database",
) {
    override fun run() {
        val config = ConfigLoader().locateAndLoad()
        val syncTask = SpeciesSync(config)

        syncTask.execute()
    }
}

class UpdateFiltersCmd : CliktCommand(
    name = "update-filters",
    help = "Update filters data",
) {
    override fun run() {
        val config = ConfigLoader().locateAndLoad()
        val task = FiltersUpdate(config)

        task.execute()
    }
}

fun main(args: Array<String>) = FuzzrakeCmd()
    .subcommands(
        TrackerCmd(),
        UrlInspectionCmd(),
        MiniaturesUpdateCmd(),
        SpeciesSyncCmd(),
        UpdateFiltersCmd(),
    )
    .main(args)
