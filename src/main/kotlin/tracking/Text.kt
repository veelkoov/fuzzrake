package tracking

data class Text(
    val original: String,
    var unused: String,
) {
    constructor(original: String): this(original, original)
}
