#!/bin/sh

UPSTREAM=${1:-'@{u}'}
LOCAL=$(git rev-parse @)
REMOTE=$(git rev-parse "$UPSTREAM")
BASE=$(git merge-base @ "$UPSTREAM")

#if [ $LOCAL = $REMOTE ]; then
    #echo "git: up-to-date"
#el
if [ $LOCAL = $BASE ]; then
    echo "git: need to pull"

    git reset --hard HEAD && git pull
    if [ $? -eq 0 ]; then
  	echo "Success: pulled from git"
    else
        echo "Failure: Could not pull from git. Script failed" >&2
        exit 1
    fi

    make init-production
    if [ $? -eq 0 ]; then
        echo "Success: make init-production"
    else
        echo "Failure: make init-production. Script failed" >&2
        exit 1
    fi

    make clearCache
    if [ $? -eq 0 ]; then
        echo "Success: make clearCache"
    else
        echo "Failure: make clearCache. Script failed" >&2
        exit 1
    fi

    make migrateDatabase
    if [ $? -eq 0 ]; then
        echo "Success: make migrateDatabase"
    else
        echo "Failure: make migrateDatabase. Script failed" >&2
        exit 1
    fi

    jq '.game.version += 1' config.json | sponge config.json

    exit 0
elif [ $REMOTE = $BASE ]; then
    echo "git: need to push"
else
    echo "git: workspace diverged!"
fi
