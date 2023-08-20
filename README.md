[![SymfonyInsight](https://insight.symfony.com/projects/a2311a3f-727c-44e4-a805-e13a7f6eeb82/mini.svg)](https://insight.symfony.com/projects/a2311a3f-727c-44e4-a805-e13a7f6eeb82) [![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)

# P8 OC DAPS - TODO LIST

For this 8th project, an existing project created using Symfony version 3.1 was provided. Starting from this project, our task was to thoroughly assess the code quality and performance to identify any anomalies. Upon detecting anomalies, we were required to draft an audit report on these findings and suggest proposals for their correction.

Additional functionalities were also outlined to be integrated into the application.

**This branch of the repository concerns the final project with anomaly fixes and the addition of functionalities.**

### DOCUMENTS
___
#### All UML diagrams of the project are available in the [diagrams](https://github.com/MH-DevApp/OC_Projet_8/tree/main/documents/diagrams) folder.

#### The documents regarding the technical authentication documentation and the audit of code quality and performance are available in the [documents](https://github.com/MH-DevApp/OC_Projet_8/tree/main/documents) folder.

### ONLINE
___
If you'd like, you can test the [ToDo & Co V3.1](https://p8-legacy.mehdi-haddou.fr) and [ToDo & Co V5.4](https://p8.mehdi-haddou.fr:3000) versions by clicking on the corresponding version. Both of these applications are running in a development environment, which is why the Profiler toolbar is available. This will allow you to test the performance of both applications.

## Specs
___
* PHP >= 8.1
* Symfony 5.4 (LTS)
* MySQL 8

## Install, build and run
___

First clone or download the source code and extract it.

### Local webserver
___
#### Requirements
- You need to have composer on your computer
- Your server needs PHP >= 8.1
- MySQL 8
- Apache or Nginx

The following PHP extensions need to be installed and enabled :
- pdo_mysql
- mysqli
- intl
- mb_string

#### Install

1. To install dependencies with Composer:

    ```bash
    > composer install
    ```

2. Creation of a `.env.dev.local` file with the following information.

    ##### Note: `*user*`, `*password*` and `*db_name*` should be replaced with your own credentials and name for your database.**

    example :
    
    ```dotenv
    DATABASE_URL="mysql://*user*:*password*@127.0.0.1:3306/*db_name*?serverVersion=8&charset=utf8mb4"
    MAILER_DSN=smtp://localhost:1025
    ```

3. To run the script for create database and load all fixtures:

    ```bash
    > composer run load
    ```

4. To launch a development server:

   **Note: Please free up port 3000 or modify it in the following command.**

    ```bash
    > php -S localhost:3000 -t public/
    ```

   or

   ```bash
   > symfony serve -port=3000
   ```

The website is available at the url: https://localhost:3000

The last coverage of tests is available at : https://localhost:3000/coverage/

### With Docker
___
#### Requirements
To install this project, you will need to have [Docker](https://www.docker.com/) installed on your Computer.

#### Install

Once your Docker configuration is up and ready, you can follow the instructions below:

1. To create a volume for the database:

    ```bash
    > docker volume create oc_dev
    ```
   
2. Creation of a `.env.dev.local` file with the following information:

    ```dotenv
    DATABASE_URL="mysql://root:password@db/oc_p8_dev?serverVersion=8&charset=utf8mb4"
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

5. To destroy/remove a Docker image, you can use the following command:

    ```bash
    > docker-compose -f ../docker-compose.dev.yml down -v --remove-orphans
    ```
   ##### The generated Docker containers uses PHP8.2, MySQL 8 and phpMyAdmin.

The website is available at the url: https://localhost:3000

The last coverage of tests is available at : https://localhost:3000/coverage/

#### DBMS

You can access the DBMS (phpMyAdmin) to view and configure your database. Please go to the url: http://localhost:8080.

- Username: `root` ;
- Password: `password`.

This assumes that you have set up a Docker container running phpMyAdmin and configured it to run on port 8080. Make sure that the Docker container is running and accessible before attempting to access phpMyAdmin.

### USERS CREDENTIALS

Accounts:
- Usernames availables: 
  - `admin`
  - `user`
- Password for all users: `123456`
