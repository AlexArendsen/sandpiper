#!/bin/bash

echo ":: Creating a Sandpiper user ::"

echo "-- Note: Sandpiper database must be installed first. Run easy_install.sh first it isn't."

echo -n "Enter Sandpiper username: "
read USERNAME
echo -n "Enter Sandpiper user password: "
read -s PASSWORD

echo ""
echo -n "Enter installed Sandpiper database name: "
read MYSQLDB
echo -n "Enter MySQL hostname: "
read MYSQLHOST
echo -n "Enter MySQL username: "
read MYSQLUSER

php create_new_user.php $USERNAME $PASSWORD | mysql $MYSQLDB --host=$MYSQLHOST --password --user=$MYSQLUSER

if [ $? -eq 0 ]; then

	echo ":: Success! ::"
	echo "New Sandpiper user created successfully!"
fi
