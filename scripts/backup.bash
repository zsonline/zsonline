#!/bin/bash

# Determine the project directory
DIR=$(cd "$(dirname "$(dirname "${BASH_SOURCE[0]}")")"; pwd -P)

# Backup the database
php "$DIR/craft" db/backup

# Backup all assets
rsync -a "$DIR/storage/assets" "$DIR/storage/backups"
rsync -a "$DIR/web/assets" "$DIR/storage/backups"
