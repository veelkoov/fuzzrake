package database.repositories

import database.helpers.getActive
import database.tables.Creators

object CreatorsRepository {
    fun countActive() = Creators.getActive().count()
}
