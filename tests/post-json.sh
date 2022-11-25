#!/bin/bash
usage() {
  echo "Usage: bash post-json.sh <FILE> <HOST>"
  echo "Example: bash post-json.sh images/test.jpg localhost/post-json.php"
}

# Print usage and exit if file and host are not provided
[ $# -ne 2 ] && usage && exit 1

fileName=$(echo $1 | sed 's:.*/::') #get everything after the last slash - extract filename.jpg from path/to/filename.jpg
base64Str=$(base64 -w 0 "$1")

curl -d "{\"file_name\": \"$fileName\", \"base64\": \"$base64Str\"}" -H 'Content-Type: application/json' $2
