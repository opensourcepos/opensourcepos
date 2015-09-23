#!/bin/bash
read -s -p "Enter Password: " mypassword
mysql -u root -p"$mypassword" -e "DROP DATABASE IF EXISTS ospos; CREATE DATABASE ospos;"
[ ! -z $1 ] && script=migrate_phppos || script=database
mysql -u root -p"$mypassword" -e "USE ospos; source $script.sql;"
