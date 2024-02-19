pipeline {
  agent {
    label 'fuzzrake'
  }

  stages {
    stage('QA') {
      steps {
        sh(label: 'Tests',    script: './gradlew test koverXmlReport koverHtmlReport')

        junit('build/test-results/test/*.xml')

        clover(
                cloverReportDir:      'build/reports/kover',
                cloverReportFileName: 'report.xml',
                healthyTarget:   [conditionalCoverage: 50, methodCoverage: 50, statementCoverage: 50],
                unhealthyTarget: [conditionalCoverage: 40, methodCoverage: 40, statementCoverage: 40],
                failingTarget:   [conditionalCoverage: 20, methodCoverage: 20, statementCoverage: 20],
        )
      }
    }
  }
}
