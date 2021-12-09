#!/bin/sh

# required in maintainer scripts
set -e

# deploy binary client
echo "Deploying abuseipdb-client ..."
ln -s /usr/lib/abuseipdb-client/bin/abuseipdb /usr/bin/abuseipdb

#if [ $? -eq 0 ]; then
    echo "Created symlink /usr/bin/abuseipdb → /usr/lib/abuseipdb-client/bin/abuseipdb."
#else
#    echo "[Error] Unable to create symlink /usr/bin/abuseipdb."
#fi
