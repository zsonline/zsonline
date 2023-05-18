#!/bin/bash

# Determine the project directory
DIR="$(dirname "$(dirname -- "$(readlink -f "${BASH_SOURCE}")")")"

# Backup the database
php "$DIR/craft" db/backup

# Backup all assets
rsync -a "$DIR/storage/assets" "$DIR/storage/backups"
rsync -a "$DIR/web/assets" "$DIR/storage/backups"
