#!/bin/bash

# Determine the project directory
DIR="$(dirname "$(dirname -- "$(readlink -f "${BASH_SOURCE}")")")"

# Backup the database
if command -v php82 &> /dev/null
then
    php82 "$DIR/craft" db/backup
else
    php "$DIR/craft" db/backup
fi

# Backup all assets
rsync -av "$DIR/storage/assets" "$DIR/storage/backups"
rsync -av "$DIR/web/assets" "$DIR/storage/backups"
