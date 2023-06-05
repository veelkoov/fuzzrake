package digests

import java.security.MessageDigest
import java.util.*

object SHA224 {
    private val digest = MessageDigest.getInstance("SHA-224")

    fun of(input: String): ByteArray = digest.digest(input.toByteArray())
    fun hexOf(input: String): String = HexFormat.of().formatHex(of(input))
}
