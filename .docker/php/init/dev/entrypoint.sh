#!/bin/bash

# Navigate to the project directory
cd /var/www/html

# Lock file to check if initial data has already been inserted
LOCKFILE=/home/first_run_done.lock

# Check if this is the first start of the container
if [ ! -f $LOCKFILE ]; then
	# Insert code to be executed only once here
	
	# Create the first run lock file
	touch $LOCKFILE
	echo "The container is starting for the first time !"
else
	echo "The container has already been started before ! Picking up where we left off..."
fi

# Call the entrypoint of the parent php image with the passed command
exec /usr/local/bin/docker-php-entrypoint "$@"