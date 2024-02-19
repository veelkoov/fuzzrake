package tracking.contents

data class CreatorItems<T>(
    val creatorData: CreatorData,
    val items: List<T>,
) {
    fun getCreatorId() = creatorData.creatorId
}
