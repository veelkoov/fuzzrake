import config.ConfigLoader
import tracking.Tracker

fun main(args: Array<String>) {
    val config = ConfigLoader()
        .locateAndLoad()
    val tracker = Tracker(config)

    tracker.run()
}
