#!/usr/bin/bash
#This Linux bash script is part of OSPOS CI4 Upgrade
#Developed and tested on Fedora 38
#Converts CI3 %x parameters into CI4 {x} parameters
#Multiple parameters must be processed individually to ensure
#correct handling of parameters in different positions depending
#upon whether the language is L to R or R to L
#This script handles an R to L typo which had the parameters entered as
#1% and 2% instead of %1 and %2 in two languages.
#The %1 rendered on screen as %1, but copied and pasted as 1% which was very confusing
#Revereses changes previously made to files in en-US language.

echo -n "fixing en-US..."
find ${1}app/Language -type f -name Items.php -exec sed -i -E -e 's/("csv_import_partially_failed")(.*?)(\{count\})(.*)/\1\2{0}\4/;' {} \;
find ${1}app/Language -type f -name Items.php -exec sed -i -E -e 's/("csv_import_partially_failed")(.*?)(\{failures\})(.*)/\1\2{1}\4/;' {} \;
echo -n "fixing others..."
find ${1}app/Language -type f -name Items.php -exec sed -i -E -e 's/("csv_import_partially_failed")(.*?)(%1|٪1|1٪|1%|% 1|٪ 1)(.*)/\1\2{0}\4/;' {} \;
find ${1}app/Language -type f -name Items.php -exec sed -i -E -e 's/("csv_import_partially_failed")(.*?)(%2|٪2|2٪|2%|% 2|٪ 2)(.*)/\1\2{1}\4/;' {} \;
