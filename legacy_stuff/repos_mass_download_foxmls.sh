#!/bin/bash

file=$1
counter=0
 
if [ $# -eq 0 ]
then
	echo "$(basename $0) file"
	exit 1
fi
 
if [ ! -f $file ]
then
	echo "$file not a file!"
	exit 2
fi
 
while read line
do

	wget -nv --content-disposition "http://beta.spokenword.ac.uk/record_view.php?pbd=$line&of=foxml"
done < $file