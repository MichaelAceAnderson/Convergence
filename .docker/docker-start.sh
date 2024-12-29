#!/bin/bash

# Usage: ./docker-start.sh [--env=name] [--rebuild] [--daemon]
# If no argument is passed to this script, the user will have to choose the docker-compose environment to use

# Change to the directory where this script is located
cd "$(dirname "$0")"

# Default option values
env=""
rebuild=false
daemon=false

# Parse the arguments passed to this script
for arg in "$@"; do
	case $arg in
		--env=*)
			# Get the value of the argument after the equal sign
			env="${arg#*=}"
			;;
		--rebuild)
			rebuild=true
			;;
		--daemon)
			daemon=true
			;;
		*)
			printf "\nUsage: $(basename "$0") [--env=name] [--rebuild] [--daemon]\n"
			printf "Options:\n"
			printf "  --env			Specifies the docker-compose environment to use (e.g., --env=samp-compose.dev.yml)\n"
			printf "  --rebuild		Forces the rebuilding of images and the recreation of containers\n"
			printf "	NOTE: If you have selected a different environment than usual, you will need to use this option for the changes to apply correctly.\n"
			printf "	Indeed, since the container names are the same, changing the environment without rebuilding will actually start the containers of the previous environment.\n"
			printf "  --daemon		Start the containers in the background\n"
			exit 1
			;;
	esac
done

# If not already done, start the docker service
# Try to use systemctl to start the docker service
sudo systemctl start docker >/dev/null 2>/dev/null
if [ $? -ne 0 ]; 
then
	# If systemctl fails, use service
	printf "\nSystemctl failed, using service to start Docker\n"
	sudo service docker start
else
	# If systemctl succeeds
	printf "\nSystemctl successfully started the docker service"
fi

# If the environment is not specified
if [ -z "$env" ]; then
	printf "\nChoose the docker-compose environment to use:\n"
	# List all the yml files in the current directory and prompt the user to choose one
	select file in $(ls -1 *.yml); do
		# If the user has chosen a file
		if [ -n "$file" ]; then
			# Get the name of the file
			docker_choice=$file
			break
		else
			printf "\nInvalid choice. Please try again.\n"
		fi
	done

else
	docker_choice=$env
fi

printf "\nYou have chosen: $docker_choice\n"

# If the user wants to force the rebuilding of images and the recreation of containers
if [ "$rebuild" = true ]; then
	# Call the container destruction script (& volumes, ...)
	# passing the file as an argument
	./docker-destroy.sh --env=$docker_choice
	# Recreate the containers of each service without cache
	printf "Rebuilding $docker_choice...\n"
	sudo docker compose -f $docker_choice build --no-cache
	# Start the docker-compose forcing the rebuilding/updating of images and the recreation of containers
	printf "Starting $docker_choice...\n"
	if [ "$daemon" = true ]; then
		sudo docker compose -f $docker_choice up --pull="missing" --build --always-recreate-deps --force-recreate --no-deps -d
	else
		sudo docker compose -f $docker_choice up --pull="missing" --build --always-recreate-deps --force-recreate --no-deps
	fi
# If the user does not want to force the rebuilding of images and the recreation of containers
else
	# Script to start the compose container normally
	printf "Starting $docker_choice...\n"
	if [ "$daemon" = true ]; then
		sudo docker compose -f $docker_choice up -d
	else
		sudo docker compose -f $docker_choice up
	fi
fi

exit 0