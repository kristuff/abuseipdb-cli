#!/bin/sh

# required in maintainer scripts
set -e

echo "Removing abuseipdb-client from /usr/bin/ ..."
\rm -f /usr/bin/abuseipdb
