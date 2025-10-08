pipeline {
    agent any

    environment {
        DOCKER_IMAGE="my-awesome-website"
        DOCKER_TAG = "${env.BUILD_NUMBER}"
        COMPOSE_PROJECT_NAME = "my-awesome-website"
    }
    
    stages {
        stage('Checkout') {
            steps {
                checkout([$class: 'GitSCM', 
                    branches: [[name: '*/master']], 
                    userRemoteConfigs: [[
                        url: 'git@github.com:ducchinhpro123/my-awesome-website.git',
                        credentialsId: 'github-ssh'
                    ]]
                ])
            }
        }

        stage('Environment Setup') {
            steps {
                sh 'cp .env.example .env'
                sh '''
                sed -i "s/MYSQL_HOST=.*/MYSQL_HOST=db/" .env
                sed -i "s/MYSQL_PORT=.*/MYSQL_PORT=3306/" .env
                sed -i "s/MYSQL_DATABASE=.*/MYSQL_DATABASE=my_awesome_website/" .env
                sed -i "s/MYSQL_USER=.*/MYSQL_USER=webapp/" .env
                sed -i "s/MYSQL_PASSWORD=.*/MYSQL_PASSWORD=webapp123/" .env
                sed -i "s/MYSQL_DATABASE_LOCAL=.*/MYSQL_DATABASE_LOCAL=demo1/" .env
                sed -i "s/ENVIRONMENT=.*/ENVIRONMENT=local/" .env
                '''
            }
        }
        
        stage('Install Dependencies') {
            steps {
                sh 'composer install --no-interaction --prefer-dist'
                sh 'composer dump-autoload'
            }
        }

        stage('Build Docker Image') {
            steps {
                sh 'docker build -t ${DOCKER_IMAGE}:${DOCKER_TAG} .'
                sh 'docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_IMAGE}:latest'
            }
        }
        
     stage('Deploy with Docker Compose') {
            when {
                branch 'master'
            }
            steps {
                sh 'docker-compose -p ${COMPOSE_PROJECT_NAME} down || true'
                sh 'docker-compose -p ${COMPOSE_PROJECT_NAME} up -d'
            }
        }
        
        stage('Deploy') {
            steps {
                // Add deployment steps
                sh 'echo "Deploying to production server..."'
                // sh 'rsync -avz ./dist/ user@your-server:/path/to/deployment/'
            }
        }
    }
    
    post {
        success {
            echo 'Build succeeded!'
        }
        failure {
            echo 'Build failed!'
        }
        always {
            // Clean up
            sh 'docker image prune -f'
        }
    }
}
