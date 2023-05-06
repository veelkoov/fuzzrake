package tracking.matchers.replace

class TxtReplace(
    private val searched: String,
    private val replacement: String,
) : AbstractReplace() {
    override fun doReplace(subject: String) = subject.replace(searched, replacement)
}
