#!/bin/bash

projectdir=$(pwd)/../../
git checkout master $projectdir/application/language
for file in $projectdir/application/language/**/*.php; do 
    f=$(basename $file)
    name=${f^}
    output=$(dirname $file)/${name%_lang.php}.php
    output=${output/application/app}
    output=${output/language/Language}
    path=${file/$projectdir\//}
    docker run -v "$projectdir:/root" php:cli-alpine -c php /root/tools/ci4-lang-migration/convert.php /root/$path > $output;
    prefix=$(basename ${output,,} .php);
    [ "$prefix" = "bootstrap_tables" ] && prefix="tables"
    sed -i "s/\(\s*\)\"${prefix}_/\1\"/g" $output
done
rm -rf "$projectdir/application"
