package tracking.website

object StandardStrategy : Strategy {
    override fun isSuitableFor(url: String) = true
}
