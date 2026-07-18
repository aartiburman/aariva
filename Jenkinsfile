pipeline {
    agent any

    stages {

        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build') {
            steps {
                bat 'composer install --no-interaction --prefer-dist'
            }
        }

        stage('Deploy') {
            steps {
                sshCommand remote: [
                    host: '82.112.239.97',
                    user: 'u434256881',
                    password: 'YOUR_SSH_PASSWORD',
                    port: 65002,
                    allowAnyHosts: true
                ], command: '''
                    cd /home/u434256881/domains/goaariva.com/public_html
                    git pull origin main
                    composer install --no-dev --optimize-autoloader
                    php artisan migrate --force
                    php artisan optimize:clear
                    php artisan optimize
                '''
            }
        }
    }

    post {
        success {
            echo '✅ AARIVA deployed successfully!'
        }

        failure {
            echo '❌ Deployment failed!'
        }
    }
}