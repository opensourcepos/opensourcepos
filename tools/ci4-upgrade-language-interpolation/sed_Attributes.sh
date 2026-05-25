#!/usr/bin/bash
#This Linux bash script is part of OSPOS CI4 Upgrade
#Developed and tested on Fedora 38
#Converts CI3 %x parameters into CI4 {x} parameters
#Multiple parameters must be processed individually to ensure
#correct handling of parameters in different positions depending
#upon whether the language is L to R or R to L
#Revereses changes previously made to files in en-US language.

echo -n "fixing en-US..."
find ${1}app/Language -type f -name Attributes.php -exec sed -i -E -e 's/("definition_error_adding_updating")(.*?)(\{definition_name\})(.*?)/\1\2{0}\4/;' {} \;
echo -n "fixing others..."
find ${1}app/Language -type f -name Attributes.php -exec sed -i -E -e 's/("definition_error_adding_updating")(.*?)(%1|٪1|1٪|1%|% 1|٪ 1)(.*)/\1\2{0}\4/;' {} \;
