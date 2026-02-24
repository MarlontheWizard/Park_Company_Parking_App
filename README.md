#Park_Company_Parking_App

![Park_App_Logo_Cropped](https://github.com/user-attachments/assets/9a3d2f56-7744-4700-aa2b-edb7676e8939)

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

Deployment (Render)
-------------------
This repository includes a Render blueprint file `render.yaml` for Docker-based deployment.

Steps:
1. Fork this repository to your GitHub account.
2. In Render, choose **New +** -> **Blueprint** and connect your fork.
3. Select this repository and deploy.
4. Render will build using `Dockerfile` and expose the app at `/`.

Notes:
- Apache document root is configured to `Web_Application/src/pages` for production.
- If your app needs API keys or credentials, configure them in Render environment variables.

Demo Checklist (Interview Ready)
--------------------------------
Use this quick sequence before sharing a demo link.

1. Start app containers:
    - `demo-start.bat`
    - or `docker-compose up --build -d`
2. Verify local app works:
    - `http://localhost:8080/Web_Application/src/pages/index.php`
3. (Optional) Expose to public internet for temporary live demo:
    - `cloudflared tunnel --url http://localhost:8080`
    - or `ngrok http 8080`
4. Validate core flow before interview:
    - Open landing page
    - Search/select a parking spot
    - Start checkout in Stripe test mode
    - Confirm success page and dashboard redirect
5. Stop demo services after use:
    - `demo-stop.bat`

Security reminder:
- Keep secrets in environment variables (`STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET`, `GOOGLE_PLACES_API_KEY`).
- Never commit API keys or webhook secrets to source control.
