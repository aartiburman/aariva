pipeline {
    agent any

    stages {

        stage('Composer Install') {
            steps {
                bat 'composer install --no-interaction --prefer-dist'
            }
        }

        stage('Create .env') {
            steps {
                bat '''
                if not exist .env (
                    copy .env.example .env
                )
                '''
            }
        }

        stage('Generate App Key') {
            steps {
                bat 'php artisan key:generate --force'
            }
        }

        stage('Storage Link') {
            steps {
                bat 'php artisan storage:link'
            }
        }

        stage('Optimize') {
            steps {
                bat 'php artisan optimize:clear'
                bat 'php artisan optimize'
            }
        }

        stage('Build Completed') {
            steps {
                echo '✅ AARIVA Build Successful'
            }
        }
    }

    post {
        always {
            cleanWs()
        }

        success {
            echo '✅ Deployment Successful'
        }

        failure {
            echo '❌ Build Failed'
        }
    }
}