package tasks.miniaturesUpdate

import java.lang.Exception

class MiniatureUrlResolverException : Exception {
    constructor(message: String, cause: Throwable) : super(message, cause)
    constructor(message: String) : super(message)
}
