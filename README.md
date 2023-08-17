[![SymfonyInsight](https://insight.symfony.com/projects/a2311a3f-727c-44e4-a805-e13a7f6eeb82/mini.svg)](https://insight.symfony.com/projects/a2311a3f-727c-44e4-a805-e13a7f6eeb82) [![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)

# P8 OC DAPS - TODO LIST

For this 8th project, an existing project created using Symfony version 3.1 was provided. Starting from this project, our task was to thoroughly assess the code quality and performance to identify any anomalies. Upon detecting anomalies, we were required to draft an audit report on these findings and suggest proposals for their correction.

Additional functionalities were also outlined to be integrated into the application.

**This branch of the repository concerns the initial project. A Docker environment and test setup have been added. Additionally, performance measurements of the application have been taken using Symfony Profiler on both a local and remote server.**

## Specs

* PHP >= 5.5.9 **AND** < 7.2
* Symfony 3.1
* Bundles installed via Composer :
    * Doctrine ORM ;
    * Symfony SwiftMailer ;
    * Symfony PHPUnit-Bridge (dev)

## Install, build and run

First clone or download the source code and extract it.

### Local webserver
___
#### Requirements
- You need to have composer on your computer
- Your server needs PHP >= 5.5.9 AND < 7.2 (Recommended PHP v5.6)
- MySQL 5.7
- Apache or Nginx

The following PHP extensions need to be installed and enabled :
- pdo_mysql
- mysqli
- intl
- mb_string
- mcrypt
- xml

#### Install

1. To install dependencies with Composer:

    ```bash
    > composer install
    ```
   
    ##### Note: At the end of the dependency installation, you will be prompted to enter the information regarding your database. This should generate a `parameters.yml` file in the `./app/config` directory.

   You can view the default example by clicking on the following link: [example](https://github.com/MH-DevApp/OC_Projet_8/blob/V3.1/app/config/parameters.yml.dist).


2. To run the script for create database and load all fixtures:

    ```bash
    > composer run load
    ```

5. To launch a development server:

   **Note: Please free up port 3000 or modify it in the following command.**

    ```bash
    > php bin/console server:start --port=3000
    ```

   or

   ```bash
   > symfony serve -port=3000
   ```

6. Clear the cache:

   ##### A caching system has been implemented in the application. If you want to reset your application's cache, execute the following command:

    ```bash
    > php bin/console c:c --env=dev
    ```

The website is available at the url: https://localhost:3000

The coverage of tests is available at : https://localhost:3000/coverage/

### With Docker
___
#### Requirements
To install this project, you will need to have [Docker](https://www.docker.com/) installed on your Computer.

#### Install

Once your Docker configuration is up and ready, you can follow the instructions below:

1. To create a volume for the database:

    ```bash
    > docker volume create oc_dev_5.7
    ```
   
2. Create `parameters.yml` file in the folder `./app/config` with the following information:

    ```yaml
    parameters:
      database_host: db
      database_port: null
      database_name: oc_p8_legacy
      database_user: root
      database_password: password
      mailer_transport: smtp
      mailer_host: 127.0.0.1
      mailer_user: null
      mailer_password: null
      secret: ThisTokenIsNotSoSecret # Change it
    ```

3. To build a Docker image:

   ##### Note: Please free up port 3000.

    ```bash
    > docker-compose -f ../docker-compose.dev.yml up -d --build --remove-orphans
    ```

4. To run the script for load all fixtures:

    ```bash
    > docker exec -it php composer run load
    ```
   
    or

    ```bash
   > composer run load-docker 
   ```

    ##### Note: The name of your PHP container may not be the same as `php`. 
    ##### Please execute the : `docker container ps` commandline in the terminal and check the names of the containers in the last column `NAMES`.

5. Clear the cache:

   ##### A caching system has been implemented in the application. If you want to reset your application's cache, execute the following command:

    ```bash
    > docker exec -it php symfony console c:c --env=dev
    ```

6. To destroy/remove a Docker image, you can use the following command:

    ```bash
    > docker-compose -f ../docker-compose.dev.yml down -v --remove-orphans
    ```
   ##### The generated Docker containers uses Ubuntu 22.04 with PHP5.6, MySQL 5.7 and phpMyAdmin.

The website is available at the url: https://localhost:3000

The coverage of tests is available at : https://localhost:3000/coverage/

#### DBMS

You can access the DBMS (phpMyAdmin) to view and configure your database. Please go to the url: http://localhost:8080.

- Username: `root` ;
- Password: `password`.

This assumes that you have set up a Docker container running phpMyAdmin and configured it to run on port 8080. Make sure that the Docker container is running and accessible before attempting to access phpMyAdmin.

### USERS CREDENTIALS

Accounts:
- Usernames availables: 
  - `user`
  - `user1`
  - `user2`
- Password for all users: `123456`

### ONLINE

If you want, you can try this application online at : https://p8-legacy.mehdi-haddou.fr
