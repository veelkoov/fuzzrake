package database.entities

import database.tables.Events
import org.jetbrains.exposed.dao.IntEntity
import org.jetbrains.exposed.dao.IntEntityClass
import org.jetbrains.exposed.dao.id.EntityID

class Event(id: EntityID<Int>) : IntEntity(id) {
    companion object : IntEntityClass<Event>(Events)

    var timestamp by Events.timestamp
    var type by Events.type
    var artisanName by Events.artisanName
    var newMakersCount by Events.newMakersCount
    var updatedMakersCount by Events.updatedMakersCount
    var reportedUpdatedMakersCount by Events.reportedUpdatedMakersCount
    var gitCommits by Events.gitCommits
    var checkedUrls by Events.checkedUrls
    var description by Events.description
    var noLongerOpenFor by Events.noLongerOpenFor
    var nowOpenFor by Events.nowOpenFor
    var trackingIssues by Events.trackingIssues
}
