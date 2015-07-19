#!/bin/bash

echo ":: Setting up Sandpiper ::"

echo -n "Enter desired Sandpiper database name: "
read MYSQLDB
echo -n "Enter MySQL hostname: "
read MYSQLHOST
echo -n "Enter MySQL username: "
read MYSQLUSER


echo "CREATE DATABASE $MYSQLDB; USE SANDPIPER;" | cat - install.sql | mysql --host=$MYSQLHOST --password --user=$MYSQLUSER

if [ $? -eq 0 ]; then
	echo ":: Success! ::"

	echo "Sandpiper database created and initialized successfully!"
	echo "Run new_user.sh to create a user"
fi

