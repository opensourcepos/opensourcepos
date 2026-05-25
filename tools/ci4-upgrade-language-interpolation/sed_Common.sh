#!/usr/bin/bash
#This Linux bash script is part of OSPOS CI4 Upgrade
#Developed and tested on Fedora 38
#Converts CI3 %x parameters into CI4 {x} parameters
#Multiple parameters must be processed individually to ensure
#correct handling of parameters in different positions depending
#upon whether the language is L to R or R to L
#Revereses changes previously made to files in en-US language.

echo -n "fixing en-US..."
find ${1}app/Language -type f -name Common.php -exec sed -i -E -e 's/("copyrights")(.*?)(\{current_year\})(.*)/\1\2{0}\4/;' {} \;
find ${1}app/Language -type f -name Common.php -exec sed -i -E -e 's/("migration_needed")(.*?)(\{version\})(.*)/\1\2{0}\4/;' {} \;
echo -n "fixing others..."
find ${1}app/Language -type f -name Common.php -exec sed -i -E -e 's/("copyrights")(.*?)(%1|٪1|1٪|1%|% 1|٪ 1)(.*)/\1\2{0}\4/;' {} \;
find ${1}app/Language -type f -name Common.php -exec sed -i -E -e 's/("migration_needed")(.*?)(%1|٪1|1٪|1%|% 1|٪ 1)(.*)/\1\2{0}\4/;' {} \;
