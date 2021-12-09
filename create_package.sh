#!/bin/sh

# our dist directory
mkdir -p dist

# create a clean debian package directory
rm -rf debian
mkdir -p debian/DEBIAN
mkdir -p debian/usr/lib/abuseipdb-client
mkdir -p debian/usr/share/doc/abuseipdb-client
mkdir -p debian/usr/share/man/man1
mkdir -p debian/etc/abuseipdb-client

# populate the debian directory
cp deb/control           debian/DEBIAN
cp deb/conffiles         debian/DEBIAN
cp deb/postinst.sh       debian/DEBIAN/postinst
cp deb/prerm.sh          debian/DEBIAN/prerm
cp config/conf.ini       debian/etc/abuseipdb-client
cp deb/copyright         debian/usr/share/doc/abuseipdb-client
cp deb/changelog         debian/usr/share/doc/abuseipdb-client/changelog.Debian
gzip -9 -n               debian/usr/share/doc/abuseipdb-client/changelog.Debian
cp LICENSE               debian/usr/lib/abuseipdb-client
cp -R bin                debian/usr/lib/abuseipdb-client
cp -R src                debian/usr/lib/abuseipdb-client
cp -R vendor             debian/usr/lib/abuseipdb-client

# convert and deploy man page
/usr/bin/pandoc --standalone --to man deb/man.md -o debian/usr/share/man/man1/abuseipdb.1
gzip -9 -n  debian/usr/share/man/man1/abuseipdb.1

# Packages should't be updated manually, but keep all source code..
cp composer.json        debian/usr/lib/abuseipdb-client
cp composer.lock        debian/usr/lib/abuseipdb-client

# adjust ownerships
chown -R root:root      debian
find debian -type d -exec chmod 0755 {} \;  #set directory attributes
find debian -type f -exec chmod 0644 {} \;  #set data file attributes
find debian/usr/lib/abuseipdb-client/bin -type f -exec chmod 0755 {} \;  #set executable attributes

# minimal permissions required for scripts
chmod 755 debian/DEBIAN/postinst
chmod 755 debian/DEBIAN/prerm

# finally build the package
dpkg-deb --build debian dist