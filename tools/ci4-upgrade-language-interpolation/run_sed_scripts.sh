#!/usr/bin/bash
#This Linux bash script is part of OSPOS CI4 Upgrade
#Developed and tested on Fedora 38
echo -n "Counting ..."
BASEURL="../../"
DONE=`find ${BASEURL}app/Language -name "*.php" -exec grep -oP "{[0-9]}" {} \; |   wc -l`
TODO=`find ${BASEURL}app/Language -name "*.php" -exec grep -oP "%[0-9]|٪[0-9]|[0-9]٪|[0-9]%|% [0-9]|٪ [0-9]|{[a-z].*?}" {} \; | wc -l`
echo " $DONE placeholders are converted and $TODO yet to do"
FILES="sed_*.sh"
for f in $FILES
do
  echo -n "Processing $f file..."
  bash "$f" "$BASEURL"
  echo "completed."
done
echo -n "Counting ..."
COMPLETE=`find ${BASEURL}app/Language -name "*.php" -exec grep -oP "{[0-9]}" {} \; |   wc -l`
MISSED=`find ${BASEURL}app/Language -name "*.php" -exec grep -oP "%[0-9]|٪[0-9]|[0-9]٪|[0-9]%|% [0-9]|٪ [0-9]|{[a-z].*?}" {} \; | wc -l`
echo " $COMPLETE placeholders are converted and $MISSED were missed"
echo "All done"
