package data

class ThreadSafe<T>(
    private val protectedItem: T,
) {
    // Assumption: ID-name pair won't be reused
    private val ownerThreadId = Thread.currentThread().id
    private val ownerThreadName = Thread.currentThread().name

    fun get(): T {
        val threadId = Thread.currentThread().id
        val threadName = Thread.currentThread().name

        if (threadId != ownerThreadId || threadName != ownerThreadName) {
            throw RuntimeException("This object's value belongs to thread $ownerThreadName ($ownerThreadId) but was accessed by $threadName ($threadId)")
        }

        return protectedItem
    }
}
