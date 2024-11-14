#!/bin/bash

#Get the container ID
CONTAINER_ID=$(docker ps -q -f name=park_app)

#Check if the container is running
if [ -z "$CONTAINER_ID" ]; then
    echo "Container is not running. Exiting..."
    exit 1
fi

#Access the container and set the Composer timeout
docker exec -it $CONTAINER_ID /bin/bash -c "composer config process-timeout 3600"

#Run composer install inside the container
docker exec -it $CONTAINER_ID /bin/bash -c "composer install"

echo "Composer installation completed inside the container."