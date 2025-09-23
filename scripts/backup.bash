#!/bin/bash

# Determine the project directory
DIR="$(dirname "$(dirname -- "$(readlink -f "${BASH_SOURCE}")")")"

# Backup the database
if command -v php84 &> /dev/null
then
    php84 "$DIR/craft" db/backup
else
    php "$DIR/craft" db/backup
fi

# Backup all assets
rsync -av --exclude transforms "$DIR/web/assets" "$DIR/storage/backups"
