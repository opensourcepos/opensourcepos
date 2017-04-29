#!/bin/bash

# Emacs
#editor='emacs +$LINE "$FILE"'

# gVim
#editor='gvim +$LINE "$FILE"'

# gEdit
#editor='gedit +$LINE "$FILE"'

# Pluma
#editor='pluma +$LINE "$FILE"'



declare -A mappings
#mappings["/remotepath"]="/localpath"


url=$1
if [ "${url:0:20}" == "editor://open/?file=" ]; then

	regex='editor\:\/\/open\/\?file\=(.+)\&line\=([0-9]+)'
	file=`echo $url | sed -r "s/$regex/\1/i"`
	line=`echo $url | sed -r "s/$regex/\2/i"`
	printf -v file "${file//%/\\x}" # decode url
	file=${file//\"/\\\"} # escape quotes

	command="${editor//\$FILE/$file}"
	command="${command//\$LINE/$line}"

	for path in "${!mappings[@]}"; do
		command="${command//$path/${mappings[$path]}}"
	done

	eval $command
fi
