#!/bin/bash
set -e

EXTDISTREPOS_URL="https://www.mediawiki.org/w/api.php?action=query&format=json&formatversion=2&list=extdistrepos"
EXTDISTREPOS=$(curl -s "$EXTDISTREPOS_URL")
EXTENSIONS=$( echo "$EXTDISTREPOS" | jq .query.extdistrepos.extensions )
EXTENSION_LIST=$( echo $EXTENSIONS | jq -c -r .[] )
SKINS=$( echo "$EXTDISTREPOS" | jq .query.extdistrepos.skins )
SKINS_LIST=$( echo "$SKINS" | jq -c -r .[] )

# Core first
echo "Downloading core..."
rm -rf core || true
curl -Lso core.zip https://github.com/wikimedia/mediawiki/archive/master.zip \
  && unzip -q core.zip \
  && mv mediawiki-master core \
  && rm core.zip

for skin in $SKINS_LIST
do
  echo "Downloading $skin"
  rm -rf "mediawiki-skins-$skin-master" || true
  rm -rf "$skin.zip" || true
  rm -rf "skins/$skin"
  curl -Lso "$skin.zip" "https://github.com/wikimedia/mediawiki-skins-$skin/archive/master.zip" \
  && unzip -q "$skin.zip" || true
  mv "mediawiki-skins-$skin-master" "core/skins/$skin" || true
  # Vector is special.
  mv "$skin-master" "core/skins/$skin" || true
  rm "$skin.zip"
done

for extension in $EXTENSION_LIST
do
  echo "Downloading $extension"
  rm -rf "mediawiki-extensions-$extension-master" || true
  rm -rf "$extension.zip" || true
  rm -rf "extensions/$extension"
  curl -Lso "$extension.zip" "https://github.com/wikimedia/mediawiki-extensions-$extension/archive/master.zip" \
  && unzip -q "$extension.zip" || true
  mv "mediawiki-extensions-$extension-master" "core/extensions/$extension" || true
  rm "$extension.zip"
done
