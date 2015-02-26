#!/bin/bash
read -s -p "Enter Password: " mypassword
mysql -u root -p"$mypassword" -e "use ospos; drop database ospos; create database ospos;"
[ ! -z $1 ] && script=migrate_phppos || script=database
mysql -u root -p"$mypassword" -e "use ospos; source $script.sql;"
