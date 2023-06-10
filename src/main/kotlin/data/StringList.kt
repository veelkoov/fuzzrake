package data

fun List<String>.pack(): String {
    return this.joinToString("\n")
}

fun String.unpack(): List<String> {
    return if (this == "") listOf() else this.split("\n")
}
