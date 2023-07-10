package data

data class ListChange(
    val before: List<String>,
    val after: List<String>,
) {
    val added = after.minus(before)
    val removed = before.minus(after)

    fun changed() = added.isNotEmpty() || removed.isNotEmpty()
}
