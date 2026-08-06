#!/usr/bin/bash
#This Linux bash script is part of OSPOS CI4 Upgrade
#Developed and tested on Fedora 38
#Converts CI3 %x parameters into CI4 {name} parameters
#Multiple parameters must be processed individually to ensure
#correct handling of parameters in different positions depending
#upon whether the language is L to R or R to L
#Revereses changes previously made to files in en-US language.

echo -n "fixing en-US..."
find ${1}app/Language -type f -name Bootstrap_tables.php -exec sed -i -E -e 's/("tables_page_from_to")(.*?)(\{pageFrom\})(.*)/\1\2{0}\4/;' {} \;
find ${1}app/Language -type f -name Bootstrap_tables.php -exec sed -i -E -e 's/("tables_page_from_to")(.*?)(\{pageTo\})(.*)/\1\2{1}\4/;' {} \;
find ${1}app/Language -type f -name Bootstrap_tables.php -exec sed -i -E -e 's/("tables_page_from_to")(.*?)(\{totalRows\})(.*)/\1\2{2}\4/;' {} \;
find ${1}app/Language -type f -name Bootstrap_tables.php -exec sed -i -E -e 's/("tables_rows_per_page")(.*?)(\{pageNumber\})(.*)/\1\2{0}\4/;' {} \;
