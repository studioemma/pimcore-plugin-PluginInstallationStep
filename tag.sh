#!/usr/bin/env bash

[[ -z $1 ]] && echo 'give a version please' && exit 1

version=$1

cat > tag-message.txt <<EOF
Pimcore PluginInstallationStep $version
====================================

*
EOF

vim tag-message.txt

git tag -a $version -F tag-message.txt

rm tag-message.txt

