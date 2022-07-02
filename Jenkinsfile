pipeline {
  agent {
    label 'fuzzrake'
  }

  stages {
    stage('Merge develop') {
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
          sh 'rm -f .env.test.local'
          sh 'echo "GOOGLE_RECAPTCHA_SITE_KEY=$GOOGLE_RECAPTCHA_SITE_KEY" >> .env.test.local'
          sh 'echo "GOOGLE_RECAPTCHA_SECRET=$GOOGLE_RECAPTCHA_SECRET" >> .env.test.local'

          sh 'rake docker-dev'
          sh 'rake composer[install]'
          sh 'yarn install'
          sh 'rake yep'
        }
      }
    }

    stage('PHPUnit') {
      steps {
        ansiColor('xterm') {
          sh 'rake pu'
        }
      }
    }

    stage('PHPStan') {
      steps {
        ansiColor('xterm') {
          sh 'rake phpstan[analyze,src,tests,--level=2]'
        }
      }
    }

    stage('PHP-CS-Fixer') {
      steps {
        ansiColor('xterm') {
          sh 'rake pcf[--dry-run]'
        }
      }
    }
  }
}
