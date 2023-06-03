plugins {
    kotlin("jvm") version "1.8.20"
    kotlin("plugin.serialization") version "1.8.20"
    application

    id("org.jetbrains.kotlinx.kover") version "0.7.1"
}

group = "it.getfursu"
version = "1.0-SNAPSHOT"

repositories {
    mavenCentral()
}

dependencies {
    implementation("org.jetbrains.kotlinx:kotlinx-serialization-json:1.3.2")
    implementation("com.fasterxml.jackson.module:jackson-module-kotlin:2.15.0")
    implementation("com.fasterxml.jackson.dataformat:jackson-dataformat-yaml:2.15.0")
    implementation("io.github.oshai:kotlin-logging-jvm:4.0.0-beta-29")
    implementation("org.slf4j:slf4j-simple:2.0.7") // TODO: Use file logging
    implementation("ch.qos.logback:logback-core:1.3.5") // TODO: Use file logging

    testImplementation(kotlin("test"))
}

tasks.test {
    useJUnitPlatform()
}

kotlin {
    jvmToolchain(11)
}

application {
    mainClass.set("MainKt")
}
