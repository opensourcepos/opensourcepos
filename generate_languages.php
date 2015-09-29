<?php

function csvstring_to_array(&$string, $CSV_SEPARATOR = ',', $CSV_ENCLOSURE = '"', $CSV_LINEBREAK = "\n") {
	$o = array ();
	$cnt = strlen ( $string );
	$esc = false;
	$escesc = false;
	$num = 0;
	$i = 0;
	while ( $i < $cnt ) {
		$s = $string [$i];
		
		while (sizeof($o) <= $num) {
			$o [] = "";
		}
				
		if ($s == $CSV_LINEBREAK) {
			if ($esc) {
				$o [$num] .= $s;
				$o [] = "";
			} else {
				$i ++;
				break;
			}
		} elseif ($s == $CSV_SEPARATOR) {
			if ($esc) {
				$o [$num] .= $s;
			} else {
				$num ++;
				$esc = false;
				$escesc = false;
			}
		} elseif ($s == $CSV_ENCLOSURE) {
			if ($escesc) {
				$o [$num] .= $CSV_ENCLOSURE;
				$escesc = false;
			}
			
			if ($esc) {
				$esc = false;
				$escesc = true;
			} else {
				$esc = true;
				$escesc = false;
			}
		} else {
			if ($escesc) {
				$o [$num] .= $CSV_ENCLOSURE;
				$escesc = false;
			}
			$o [$num] .= $s;
		}
		$i ++;
	}
	return $o;
}

$dir = new DirectoryIterator(__DIR__ . "/translations");
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
        $file = $fileinfo->getFilename();
        // temporary skip validation file (should be inside the system/language folder)
        if (strstr($file, 'form_validation_lang.csv')) continue;
        print_r("generating $file...\n");
        $fh = fopen ( __DIR__ . "/translations/" . $file, 'r' );
        $language_files = array ();
        $output_base = './application/language/';

        // find out all lang files (columns) we're dealing with...
        $header = fgetcsv ( $fh );
        foreach ( $header as $h ) {
            if ($h != 'label') {
                $language_files [] = $h;
            }
        }

        // make a directory in the output folder for each language
        foreach ( $language_files as $l ) {
        	if (!file_exists($output_base . $l)) {
    	        mkdir ( $output_base . $l, 0777, true );
        	}
        }

        foreach ( $language_files as $key => $l ) {
            $index_files = array ();
            rewind ( $fh );
            $header = fgetcsv ( $fh ); // move cursor past header row

            // iterate through all rows of the csv
            $lfh = false;

            while ( $line = fgets ( $fh, 9999999 ) ) {
                $line = csvstring_to_array ( $line, ',', '"', "\n" );
                if (! sizeof($line))
                    continue; // this is a blank line between groups
                $index_file_name = basename($file, ".csv");
                $key_name = $line[0];
                
                // have we see this before? no == make new file
                if (! in_array ( $index_file_name, $index_files )) {

                    $index_files [] = $index_file_name;

                    // no -- make new file, close existing if open (not 1st)
                    if ($lfh !== false) {
                    	write ( $lfh, "?>" );
                        fclose ( $lfh );
                    }

                    $lfh = fopen ( $output_base . $l . '/' . $index_file_name . '.php', 'w' );
                    fwrite ( $lfh, '<?php ' . PHP_EOL . PHP_EOL );
                    fwrite ( $lfh, '$lang["' . $key_name . '"] = "' . str_replace ( '"', '\"', $line [($key + 1)] ) . '";' . PHP_EOL );
                } else {
					if (sizeof($line) > 2) {
	                    // yes -- add to file we're working on
    	                fwrite ( $lfh, '$lang["' . $key_name . '"] = "' . str_replace ( '"', '\"', $line [($key + 1)] ) . '";' . PHP_EOL );
					}
                }
            }
        }
    }
}


?>
