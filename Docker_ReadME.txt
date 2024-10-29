Here are some general instructions on how to set up Docker on your system. 

1. Download and install the Docker Desktop GUI on your system (mac/windows). 
   Set it up to integrate with your linux environment if possible. 
   For example, on Windows you can use WSL2. 
   e.g Windows -> Install WSL2 -> Go to resources in Docker GUI and check off integration

2. Install the Docker package on your linux distribution (e.g ubuntu)

3. Install the Apache package in linux. For example -> sudo apt install apache2 

4. From your linux environment and in the Park_App directory, run the docker compose file. 
    e.g on Ubuntu -> docker-compose up --build
    Check the Docker Desktop GUI to see if it is running, click the 8080 link to redirect to 
    the page. If you see the PHP homepage then your docker is set up correctly. 
    **TIP**: On my system, the Docker Desktop GUI has to be running for the docker commands to execute. 
             Run the GUI if the compose command is not found. 

