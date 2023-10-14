<?php

include($argv[1]);

echo "<?php\n";
echo "return [\n";
foreach ($lang as $key=>$value) {
    echo "\t\"$key\" => \"$value\",\n";
}
echo "];";
?>

