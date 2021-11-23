#!/bin/sh

# our dist directory
mkdir -p dist

# create a clean debian package directory
rm -rf debian
mkdir -p debian/DEBIAN
mkdir -p debian/usr/lib/abuseipdb-client
mkdir -p debian/usr/share/doc/abuseipdb-client
mkdir -p debian/etc/abuseipdb-client

# populate the debian directory
cp deb/control           debian/DEBIAN
cp deb/postinst.sh       debian/DEBIAN/postinst
cp deb/prerm.sh          debian/DEBIAN/prerm
cp config/conf.ini       debian/etc/abuseipdb-client
cp deb/copyright         debian/usr/share/doc/abuseipdb-client
cp LICENSE               debian/usr/lib/abuseipdb-client
cp -R bin                debian/usr/lib/abuseipdb-client
cp -R src                debian/usr/lib/abuseipdb-client
cp -R vendor             debian/usr/lib/abuseipdb-client

# Packages should't be updated manually, do not deploy composer files.
#cp composer.json        debian/usr/lib/abuseipdb-client
#cp composer.lock        debian/usr/lib/abuseipdb-client

# adjust ownerships
chown -R root:root      debian
chmod -R 0755           debian/usr/lib/abuseipdb-client

# minimal permissions required for scripts
chmod 755 debian/DEBIAN/postinst
chmod 755 debian/DEBIAN/prerm

# finally build the package
dpkg-deb --build debian dist