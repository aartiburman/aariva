pipeline {
    agent any

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Composer') {
            steps {
                bat 'composer install'
            }
        }

        stage('Laravel') {
            steps {
                bat 'php artisan optimize:clear'
                bat 'php artisan optimize'
            }
        }

        stage('Done') {
            steps {
                echo 'AARIVA Build Successful'
            }
        }
    }
}