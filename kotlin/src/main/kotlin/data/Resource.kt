package data

object Resource {
    fun read(name: String): String {
        return javaClass.getResource(name)!!.readText()
    }
}
