package tracking.steps

import tracking.Text
import tracking.matchers.Factory
import tracking.matchers.Matchers
import tracking.snapshots.Snapshot

class Preprocessor {
    private val cleaners = Factory.getCleaners()

    fun preprocess(input: String): String
    {
        var result = cleaners.replaceIn(input)
        // TODO
        return result
    }
}
