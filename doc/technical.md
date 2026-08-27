# AbuseIPDB CLI Technical Details

[![Build Status](https://scrutinizer-ci.com/g/kristuff/abuseipdb-cli/badges/build.png?b=master)](https://scrutinizer-ci.com/g/kristuff/abuseipdb-cli/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kristuff/abuseipdb-cli/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kristuff/abuseipdb-cli/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/kristuff/abuseipdb-cli/v/stable)](https://packagist.org/packages/kristuff/abuseipdb-cli)
[![License](https://poser.pugx.org/kristuff/abuseipdb-cli/license)](https://packagist.org/packages/kristuff/abuseipdb-cli)
[![Total Downloads](https://poser.pugx.org/kristuff/abuseipdb-cli/downloads)](https://packagist.org/packages/kristuff/abuseipdb-cli)

## Features 

- Single IP check request **✓** 
- IP block check request **✓** 
- Blacklist request **✓** 
- Single IP report request **✓** 
- Bulk report request (send `csv` file) **✓** 
- Clear IP address request (remove your own reports) **✓**
- Auto cleaning report comments from sensitive data (email, custom ip/domain names list) **✓** 
- Colored reports, JSON or plaintext output **✓** 
- Easy Fail2ban integration **✓** 

## Requirements 

- PHP >= 7.1
- PHP's cURL  
- A valid [abuseipdb.com](https://abuseipdb.com) account with an API key
- Composer for install (not required if you use .deb package)

## Dependencies 

- [kristuff/abuseipdb](https://github.com/kristuff/abuseipdb) A wrapper for AbuseIPDB API v2
- [kristuff/mishell](https://github.com/kristuff/mishell) Used to build CLI colored tables/reports


## Install 

#### Debian based distros 

Since v0.9.14, abuseipdb-client is available as debian package (`.deb`). Packages are available on [packages.kristuff.fr/debian/](https://packages.kristuff.fr/debian/). You can configure `apt` to connect kristuff repository (see instructions here: [packages.kristuff.fr/](https://packages.kristuff.fr/)) and install it: 

```.bash
apt-get update
apt-get install abuseipdb-client
```

Alternatively, you can download the latest package (from release tags on github or from repository) and install it using `dpkg -i`:

```.bash
# -----------------------------------------------
# /!\ Please change "X.X.X" to the latest version
# https://packages.kristuff.fr/debian/pool/main/a/abuseipdb-client/
# -----------------------------------------------
wget https://packages.kristuff.fr/debian/pool/main/a/abuseipdb-client/abuseipdb-client_X.X.X_all.deb
dpkg -i abuseipdb-client_X.X.X_all.deb
# OR
apt install ./abuseipdb-client_X.X.X_all.deb
```

Main library is deployed to `/usr/lib/abuseipdb-client/` and executable (symlink) to `/usr/bin/`. Since v0.9.15, the config files are located in `/etc/abuseipdb-client/`. 

#### Other distros 

You need composer to install the project.

-  Install project and dependencies with composer `create-project` command. This will create a folder named *abuseipdb-cli* in the current directory and install dependencies:

    ```.bash
    $  composer create-project kristuff/abuseipdb-cli
    ```

    The project structure should be the following:

    ```.apacheconf
    abuseipdb-cli/
        |_ bin/...
        |_ config/...
        |_ src/...
        |_ vendor/...
        composer.json
        composer.lock
        LICENCE
    ```

-  Make sure the binary file `abuseipdb`, located in the `bin` folder at the project root, is executable:

    ```.bash
    $ chmod +x /YOUR_PATH/abuseipdb-cli/bin/abuseipdb
    ```

To use it more easily and depending on how you want to use it (from terminal only or with other scripts), you could: 

- deploy the bin file to `/usr/local/bin/` or `/usr/sbin/`:

    >  This task requires **root** or **administrator** permissions.

    ```.bash
    ln -s  /YOUR_PATH/abuseipdb-cli/bin/abuseipdb  /usr/local/bin/
    ```

- or create an `alias`:

    ```.bash
    $ alias abuseipdb='php /YOUR_PATH/abuseipdb-cli/bin/abuseipdb'
    ```

## Configuration 

In manual install, the configuration file (`conf.ini`) is located in the `config` directory. In case package is installed globally, configuration file is located in `/etc/abuseipdb-client/`. 

In most of the cases you should not modify the `conf.ini` file directly but provide customizations in a `local.ini` file under the same directory. This prevents to loose changes during update. 

You must at least define your **api key**. Other fields are optional. 

### Setup/change API key {#api_key}

Edit the configuration file `conf.ini`/`local.ini` and set up the `api_key` property in the `[common]` section.

```.apache-conf   
[common]
api_key= "123456789"
```

### Setup self IPs

If you plan to automate reports with [**Fail2ban**](#fail2ban) and send logs lines as message (***matches***), it's recommended to configure a ***whitelist*** of IPs and names (domain name) you want to exclude from message. When you use the `report` command, comments are checked and fields that match to an entry in the list are replaced by `*`. Note that email addresses are already replaced.

To use a whitelist, edit the configuration file `conf.ini`/`local.ini` and set up the `self_ips` property in the `[report]` section like this:

```.apache-conf   
[report]
self_ips= "xx9999.ip-256-256-256.xx ,256.256.256.256, subdomain.example.com,example.com, example"
```

> Ips or names **must** be comma separated, and **may** contain spaces (will be trimmed). Order **does** matter: Enter subdomains first to prevent to replace a part of field only.  


### Setup default timeout

To setup a default timeout, locate and edit the configuration then set the `timeout` property in the `[common]` section.
Timeout is expressed in milliseconds. **0** means no timeout.

Timeout will apply to all API requests.

```.apache-conf   
[common]
; setup a default timeout of 5 secs
timeout= 5000
```

> You can overwrite the default value in command line with the `-t` | `--timeout` option.

## Fail2ban integration 

**AbuseIPDB** provides a [tutorial](https://www.abuseipdb.com/fail2ban.html) about integrating their API with **Fail2Ban** to automatically report bad IPs you should read first. 

The default action for reporting an ip address, definided in file `action.d/abuseipdb.conf`, sends logs lines as message (***matches***) while they may contain sensitive data. 

You can use in place **kristuff/abuseipdb-cli** and its built-in filter in `report` command (see [Report a single IP address](https://to.change/doc#report)):

-    **1/** Check your have Fail2Ban v0.10.0 or later installed (`fail2ban-client -V`) and check the file `/etc/fail2ban/jail.conf` or `/etc/fail2ban/jail.local` (the customizable version) contains the following definition for ***action_abuseipdb***: 

    ```.bash
    # Report ban via abuseipdb.com.
    #
    # See action.d/abuseipdb.conf for usage example and details.
    #
    action_abuseipdb = abuseipdb
    ```

-   **2/** Make sure the file `/etc/fail2ban/action.d/abuseipdb.conf` exists, and overwrite the `actionban` by creating a file `/etc/fail2ban/action.d/abuseipdb.local` with the following content (Adjust the binary path depending on your install, below `/usr/bin/`): 

    ```.bash
    [Definition]
    actionban = /usr/bin/abuseipdb -R "<ip>" -c "<abuseipdb_category>" -m "<abuseipdb_comment>" > /dev/null
    ```

-   **3/** In the file `/etc/fail2ban/jail.local`, for each jail you want report IP each time an IP is banned, add a line to the `action` definition:

    ```.bash 
    abuseipdb[abuseipdb_category=" ... ", abuseipdb_comment=" ..."]
    ```

    > The following examples suppose your default action is `%(action_)s` in your jails.

    - example for reporting IP for web hack attempts using the log lines (***matches***) as comment:  

    ```.bash
    action = %(action_)s
            abuseipdb[abuseipdb_category="webattack", abuseipdb_comment="<matches>"]
    ```

    - example for reporting IP for port scan using a custom message with IP as comment :  

    ```.bash
    action = %(action_)s
            abuseipdb[abuseipdb_category="scan", abuseipdb_comment="Port scan detected from \[<ip>\]"]
    ```

    - example for reporting IP for brute force on ssh using a custom message with IP as comment:  

    ```.bash
    action   = %(action_)s
            abuseipdb[abuseipdb_category="ssh,brute", abuseipdb_comment="SSH brute force attack detected from \[<ip>\]"]
    ```

- **4/** Restart or reload Fail2Ban

```.bash
fail2ban-client reload
```

## AbuseIPDB API Daily Rate Limits 

The API daily rate limits are currently as follows:

Commands        |    Standard   |	Webmaster 	|   Supporter 	|   Basic Subscription 	    |   Premium Subscription
---             |   ---         |   ---         |   ---         |   ---                     |   ---
check 	        |   1,000 	    |   3,000 	    |   5,000 	    |   10,000 	                |   50,000
blacklist 	    |   5 	        |   10 	        |   20 	        |   100                     |	500
report 	        |   1,000 	    |   1,000 	    |   5,000       |	10,000                  |	50,000
check-block 	|   100 	    |   250         |	500         |	1,000                   |	5,000
bulk-report 	|   5 	        |   10 	        |   20 	        |   100 	                |   500
clear-address 	|   5 	        |   10 	        |   20 	        |   100 	                |   500

> Upon reaching your daily limit, you will receive an error (HTTP 429 Too Many Requests).
