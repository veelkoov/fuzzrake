package data

import com.fasterxml.jackson.databind.ObjectMapper
import com.fasterxml.jackson.dataformat.yaml.YAMLFactory
import com.fasterxml.jackson.module.kotlin.KotlinModule
import java.io.File

object Yaml {
    private val mapper = ObjectMapper(YAMLFactory())

    init {
        mapper.registerModule(KotlinModule.Builder().build())
    }

    fun <T> readFrom(file: File, valueType: Class<T>): T {
        return mapper.readValue(file, valueType)
    }

    fun <T> parse(string: String, valueType: Class<T>): T {
        return mapper.readValue(string, valueType)
    }
}
