package database.entities

import database.tables.Events
import org.jetbrains.exposed.dao.IntEntity
import org.jetbrains.exposed.dao.IntEntityClass
import org.jetbrains.exposed.dao.id.EntityID

class Event(id: EntityID<Int>) : IntEntity(id) {
    companion object : IntEntityClass<Event>(Events)

    var timestamp by Events.timestamp
    var type by Events.type
    var creatorName by Events.creatorName
    var newCreatorsCount by Events.newCreatorsCount
    var updatedCreatorsCount by Events.updatedCreatorsCount
    var reportedUpdatedCreatorsCount by Events.reportedUpdatedCreatorsCount
    var gitCommits by Events.gitCommits
    var checkedUrls by Events.checkedUrls
    var description by Events.description
    var noLongerOpenFor by Events.noLongerOpenFor
    var nowOpenFor by Events.nowOpenFor
    var trackingIssues by Events.trackingIssues
}
