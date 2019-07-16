#!/bin/bash

cd "$( dirname "${BASH_SOURCE[0]}" )"

# This script takes blueprints/_template.apib, and generates *.apibs
# for each model. Then, it concatenates all blueprints/*.apib into
# apiary.apib, for use in Dredd, etc.

# I feel iffy about generating tests, but honestly, maybe it's to
# our credit that the system we are creating is consistent enough
# to pull off something like this. At the end of the day, it's the
# Data Structures that we really care about. Nothing else *should*
# vary from model to model.

# When you make changes to any model, update its Data Structure
# (see blueprints/_data.apib), and re-run this script.

# When you add a new model, do that, *and* edit the config below
# before re-running this script.


# Bash doesn't support multi-dimensional arrays, so we gotta make do.
# Add comma-separated info for each of the models to this array.
# Whitespace doesn't matter, *but* make sure to use spaces, not tabs.
# IDs are used to test valid requests.

# FIELDS: singular, plural, two valid IDs

MODELS=(
	"image, images, 39d43108-e690-2705-67e2-a16dc28b8c7f, c418c213-c246-1883-cd09-ca8496c69c9a"
	"sound, sounds, ca6313d3-ecc4-8e21-66b5-421a484a9886, 91f6ccbe-471f-b1ef-4505-67af127e626c"
	"text,  texts,  16f17e2b-5c8c-8640-59be-ece0cd06d0ae, c9bdc993-5c4b-edab-4889-9ba7ccd471c9"
	"video, videos, c051f71e-2b69-ac68-9aa8-99410d91f3f3, eb06edce-6f2e-727c-0cee-a32cef589911"
)

DESTINATION="apiary.apib";

# We need this to automatically capitalize model names
# https://stackoverflow.com/a/12487465/1943591
# https://stackoverflow.com/a/6212408/1943591
# https://stackoverflow.com/a/1541178/1943591
function capitalize {

	# This first solution only capitalized the first word, not all
	# IN="$1"; echo "$(tr '[:lower:]' '[:upper:]' <<< ${IN:0:1})${IN:1}"

	awk '{for(i=1;i<=NF;i++){ $i=toupper(substr($i,1,1)) substr($i,2) }}1' <<< "$1"

}

# We'll be modifying copies of the template string
TEMPLATE="$(cat blueprints/_template.apib)"

for MODEL in "${MODELS[@]}"
do

	# Turn it into an array. Incidentally "trims" spaces
	# https://stackoverflow.com/a/5257398/1943591
	MODEL=(${MODEL//,/ })

	# Start building the test file
	OUT="$TEMPLATE"

	# Substitute hyphens with spaces
	ENTITY=$(tr -s '-' ' ' <<< "${MODEL[0]}")
	ENTITIES=$(tr -s '-' ' ' <<< "${MODEL[1]}")

	# Prime capitals
	ENTITY=$(capitalize "${ENTITY}")
	ENTITIES=$(capitalize "${ENTITIES}")

	# Replace lowercase entity names
	OUT="${OUT//entity/${MODEL[0]}}"
	OUT="${OUT//entities/${MODEL[1]}}"

	# Replace capitalized entity names
	OUT="${OUT//Entity/$ENTITY}"
	OUT="${OUT//Entities/$ENTITIES}"

	# Replace ids
	OUT="${OUT//"id[0]"/${MODEL[2]}}"
	OUT="${OUT//"id[1]"/${MODEL[3]}}"

	# Save the file
	echo "$OUT" > "blueprints/${MODEL[1]}.apib"

done

# Next, combine all blueprint files into apiary.apib
# First, prepare our "header" and "footer"
# By footer, I mean the "Data Structures" file
HEADER="$(cat blueprints/_header.apib)"
FOOTER="$(cat blueprints/_data.apib)"

# Start building an array of file contents
SECTIONS=("$HEADER")

# This gets all blueprint/*.apib files that don't start with _
# https://stackoverflow.com/a/21368889/1943591
# https://stackoverflow.com/q/20138397/1943591
while read -r line
do
	# Can't do cat blueprints/${FILE} for some reason...
	CONTENT="$(cd blueprints && cat $line)"

	# https://stackoverflow.com/q/1951506/1943591
	SECTIONS+=("$CONTENT")

done < <(ls blueprints | grep "^[^_].*apib$")

# Add the footer...
SECTIONS+=("$FOOTER")

# Clear the existing destination file
if [ -f $DESTINATION ]; then
	rm "$DESTINATION"
fi

touch "$DESTINATION"

# Append each item in SECTIONS to destination
for SECTION in "${SECTIONS[@]}"
do

	echo "$SECTION" >> "$DESTINATION"

	# Trailing newlines at EOF were removed though cat
	# https://stackoverflow.com/q/18226491/1943591
	# We will restore them here
	echo $'\n\n\n\n\n\n' >> "$DESTINATION"

done

# Trim the last trailing newlines by resaving the file
echo "$(cat "$DESTINATION")" > "$DESTINATION"
