pipeline {
    agent any

    environment {
        PHP = "php"
        COMPOSER = "composer"
    }

    stages {

        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Composer Install') {
            steps {
                bat '%COMPOSER% install --no-interaction --prefer-dist'
            }
        }

        stage('Environment') {
            steps {
                bat '''
                if not exist .env (
                    copy .env.example .env
                )
                '''
            }
        }

        stage('Generate Key') {
            steps {
                bat '%PHP% artisan key:generate'
            }
        }

        stage('Storage Link') {
            steps {
                bat '%PHP% artisan storage:link'
            }
        }

        stage('Migration') {
            steps {
                bat '%PHP% artisan migrate --force'
            }
        }

        stage('Optimize') {
            steps {
                bat '%PHP% artisan optimize:clear'
                bat '%PHP% artisan optimize'
            }
        }

        stage('Finished') {
            steps {
                echo 'AARIVA Build Successful'
            }
        }
    }

    post {
        success {
            echo 'Deployment Successful'
        }

        failure {
            echo 'Build Failed'
        }
    }
}
