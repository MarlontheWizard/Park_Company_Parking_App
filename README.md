#Park_Company_Parking_App

Docker Container Setup
----------------------
Assuming Docker Desktop and the necessary CMD package is installed on your machine, fetch and pull the repo. 
In the terminal that has Docker, make sure that the current working directory is the Park_App directory.
Also, remember that on most machines the docker desktop app must be running for your docker commands to work.  

Step #1: Type and enter the following command --> docker-compose up --build 
         The Docker Container will be built and all necessary packages and dependencies will be installed, this should show in the terminal. 
         When finished, check the desktop app to view the container visually. Here you'll find the log and files for the container. 
         
Step #2: Click on the port link (it is blue and underlined) under the container name to visit page on internet. If it says Forbidden it is because
         we have not set the PHP for our homepage yet. To test and ensure our server is working, go to http://localhost:8080/Web_Application/src/pages/index.php 
         **Notice the structure of the URL, follow this structure to load the other pages**

[Optional] Step #3: To open container in the terminal, make sure that the container is running and enter -> docker ps 
                    Save the Container_ID and then enter -> docker exec -it <Container_ID> /bin/bash 
                    This should log you into the container environment, notice the directory is var/www/html. 


composer.json
--------------
Contains dependencies for web app. **Any new libraries or dependencies should be installed by updating this file**

google/apiclient  -> Google API Client Library for PHP -> Enables Google Authentication and interaction with Google services. 

vlucas/phpdotenv -> PHP dotenv is a library to load environment variables from a .env file -> Facilitates the management of sensitive information such as API keys and credentials without hardcoding them in the codebase. 

phpunit/phpunit -> A unit testing framework for PHP -> Provides tools for writing and running unit tests to ensure code quality and functionality. This is just in case for QA. |
