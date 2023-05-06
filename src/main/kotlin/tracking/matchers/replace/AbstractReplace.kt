package tracking.matchers.replace

import tracking.matchers.Matchable

abstract class AbstractReplace : Matchable {
    private var wasUsed = false

    protected abstract fun doReplace(subject: String): String

    override fun replaceIn(subject: String): String {
        val result = doReplace(subject)

        if (result != subject) {
            wasUsed = true
        }

        return result
    }

    override fun wasUsed() = wasUsed
}