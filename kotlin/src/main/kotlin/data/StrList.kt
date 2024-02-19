package data

typealias StrList = List<String>

fun StrList.pack(): String {
    return this.joinToString("\n")
}

fun String.unpack(): StrList {
    return if (this == "") listOf() else this.split("\n")
}
