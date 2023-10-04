#!/usr/bin/bash
#This Linux bash script is part of OSPOS CI4 Upgrade
#Developed and tested on Fedora 38
#This script fixes a typo in the target file: {} was used instead of [].
find ${1}app/Language -type f -name Common.php -exec sed -i -E -e 's/("none_selected_text")(.*?)(\{Inget valt\})(.*)/\1\2[Inget valt]\4/g' {} \;
