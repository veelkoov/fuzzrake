plugins {
    kotlin("jvm") version "2.1.0"
    kotlin("plugin.serialization") version "2.1.0"
    application

    id("org.jetbrains.kotlinx.kover") version "0.7.4"
    id("jacoco")
}

group = "it.getfursu"
version = "git-HEAD"

repositories {
    mavenCentral()
}

val exposedVersion: String by project
val mockkVersion: String by project
val ktorVersion: String by project

dependencies {
    // Command-line parsing
    implementation("com.github.ajalt.clikt:clikt:4.2.1")

    // JSON and YAML support
    implementation("org.jetbrains.kotlinx:kotlinx-serialization-json:1.6.0")
    implementation("com.fasterxml.jackson.module:jackson-module-kotlin:2.15.3")
    implementation("com.fasterxml.jackson.dataformat:jackson-dataformat-yaml:2.15.3")

    // Logging
    implementation("io.github.oshai:kotlin-logging-jvm:5.1.0")
    implementation("org.apache.logging.log4j:log4j-api:2.21.1")
    implementation("org.apache.logging.log4j:log4j-core:2.21.1")
    implementation("org.apache.logging.log4j:log4j-slf4j2-impl:2.21.1")

    // Database
    implementation("org.jetbrains.exposed:exposed-core:$exposedVersion")
    implementation("org.jetbrains.exposed:exposed-dao:$exposedVersion")
    implementation("org.jetbrains.exposed:exposed-java-time:$exposedVersion")
    implementation("org.jetbrains.exposed:exposed-jdbc:$exposedVersion")
    implementation("org.xerial:sqlite-jdbc:3.42.+")

    // HTML parsing
    implementation("org.jsoup:jsoup:1.16.1")

    // HTTP client
    implementation("io.ktor:ktor-client-core:$ktorVersion")
    implementation("io.ktor:ktor-client-encoding:$ktorVersion")
    implementation("io.ktor:ktor-client-java:$ktorVersion")
    implementation("io.ktor:ktor-client-logging:$ktorVersion")

    // Tests
    testImplementation(kotlin("test"))
    testImplementation("io.mockk:mockk:$mockkVersion")
    testImplementation("io.ktor:ktor-client-mock:$ktorVersion")
}

jacoco {
    toolVersion = "0.8.11"
}

kotlin {
    jvmToolchain(17)
}

application {
    mainClass.set("FuzzrakeKt")
}

tasks.build {
    finalizedBy(tasks.installDist)
}

tasks.distTar {
    enabled = false
}

tasks.distZip {
    enabled = false
}

tasks.test {
    useJUnitPlatform()

    finalizedBy(tasks.jacocoTestReport)
}

tasks.jacocoTestReport {
    dependsOn(tasks.test)

    reports {
        xml.required.set(false)
        html.required.set(true)
        csv.required.set(false)
    }
}
