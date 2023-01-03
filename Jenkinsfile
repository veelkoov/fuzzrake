pipeline {
  agent {
    label 'fuzzrake'
  }

  stages {
    stage('Trigger branches builds') {
      when {
        branch 'develop'
      }

      steps {
        script {
          sh(
              script: '''git branch -a | grep '^  remotes/origin/' | cut -d/ -f3-''',
              returnStdout: true,
          )
              .tokenize("\n")
              .each { branch ->
                if (!['main', 'beta', 'develop'].contains(branch)) {
                  try {
                    build(
                        job: currentBuild.fullProjectName.replaceFirst(~/\/${env.BRANCH_NAME}$/, "/$branch"),
                        wait: false,
                    )
                  } catch (error) {
                    echo(error.getMessage())
                  }
                }
              }
        }
      }
    }

    stage('Merge develop') {
      when {
        not {
          branch 'develop'
        }
      }

      steps {
        ansiColor('xterm') {
          sh 'git merge --no-edit origin/develop'
        }
      }
    }

    stage('Install') {
      environment {
        GOOGLE_RECAPTCHA_SITE_KEY = credentials('GOOGLE_RECAPTCHA_SITE_KEY')
        GOOGLE_RECAPTCHA_SECRET = credentials('GOOGLE_RECAPTCHA_SECRET')
      }

      steps {
        ansiColor('xterm') {
          dir('tests/test_data/statuses') {
            git(branch: 'main', poll: false, url: env.FUZZRAKE_STATUSES_TEST_DATA_GIT_URI)
          }

          sh 'rm -f .env.test.local'
          sh 'echo "GOOGLE_RECAPTCHA_SITE_KEY=$GOOGLE_RECAPTCHA_SITE_KEY" >> .env.test.local'
          sh 'echo "GOOGLE_RECAPTCHA_SECRET=$GOOGLE_RECAPTCHA_SECRET" >> .env.test.local'

          sh './toolbox docker-up'
          sh './toolbox composer install'
          sh 'yarn install'
          sh './toolbox yep'
          sh './toolbox pu --version'
          sh './toolbox console doctrine:schema:create'
        }
      }
    }

    stage('QA') {
      parallel {
        stage('PHPUnit') {
          steps {
            ansiColor('xterm') {
              sh './toolbox pu --log-junit junit-results.xml --coverage-clover clover-results.xml --coverage-html coverage-results'
            }
          }

          post {
            always {
              junit 'junit-results.xml'

              clover cloverReportDir: '.',
                cloverReportFileName: 'clover-results.xml',
                failingTarget:   [conditionalCoverage: 20, methodCoverage: 20, statementCoverage: 20],
                healthyTarget:   [conditionalCoverage: 50, methodCoverage: 50, statementCoverage: 50],
                unhealthyTarget: [conditionalCoverage: 40, methodCoverage: 40, statementCoverage: 40]
            }
          }
        }

        stage('PHP-CS-Fixer') {
          steps {
            ansiColor('xterm') {
              sh './toolbox pcf --dry-run --diff'
            }
          }
        }

        stage('PHPStan') {
          steps {
            ansiColor('xterm') {
              sh './toolbox ps -v'
            }
          }
        }

        stage('Rector') {
          steps {
            ansiColor('xterm') {
              sh './toolbox rector --dry-run'
            }
          }
        }
      }
    }
  }
}
