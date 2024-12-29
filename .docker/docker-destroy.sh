#!/bin/bash

# Usage: ./docker-destroy.sh --env=name
# If no environment is passed as a parameter to this script,
# the user will be asked to choose the docker-compose environment to use

# Default option values
env=""

# Parse the arguments passed to this script
for arg in "$@"; do
	case $arg in
		--env=*)
			# Retrieve the value of the argument after the equal sign
			env="${arg#*=}"
			;;
		*)
			printf "\nUsage: $(basename "$0") [--env=name]\n"
			printf "Options:\n"
			printf "  --env			Specifies the docker-compose environment to use (e.g., --env=dev)\n"
			exit 1
			;;
	esac
done

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

# Shut down the containers of the specified environment
# remove containers and networks
printf "Shutting down the composition $docker_choice and removing volumes...\n"
sudo docker compose -f $docker_choice down --volumes
# Remove the containers of the specified environment
printf "Removing the containers of the composition $docker_choice...\n"
sudo docker compose -f $docker_choice rm -f

exit 0
