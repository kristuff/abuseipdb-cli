# kristuff/abuseipdb-cli
The CLI version of [kristuff/abuseipdb](https://github.com/kristuff/abuseipdb), a mini library to work with the AbuseIPDB API V2

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kristuff/abuseipdb-cli/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kristuff/abuseipdb-cli/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/kristuff/abuseipdb-cli/badges/build.png?b=master)](https://scrutinizer-ci.com/g/kristuff/abuseipdb-cli/build-status/master)
[![Latest Stable Version](https://poser.pugx.org/kristuff/abuseipdb-cli/v/stable)](https://packagist.org/packages/kristuff/abuseipdb-cli)
[![License](https://poser.pugx.org/kristuff/abuseipdb-cli/license)](https://packagist.org/packages/kristuff/abuseipdb-cli)

![sample-report)](doc/sample-report.png)

Features
--------
- **✓** Single check request
- **✓** Single report request
- *\[TODO\]* Check block request  
- *\[TODO\]* Bulk report request

Requirements
------------
- PHP >= 7.1
- PHP's cURL  
- A valid [abuseipdb.com](https://abuseipdb.com) account with an API key

Dependencies
------------
- [kristuff/abuseipdb](https://github.com/kristuff/abuseipdb) The library to communicate with the abuseIPDB API v2
- [kristuff/mishell](https://github.com/kristuff/mishell) Used to build cli colored/tables reports

Install
-------

1. Install with composer

    ```bash
    $ mkdir abuseipdb-cli
    $ cd abuseipdb-cli
    $ composer require kristuff/abuseipdb-cli
    $ composer install
    ```

2. Edit the `config.json` file locate in the `config` path and define your **api key** and you **user id**.

    ```json
    {
        "api_key": "YOUR ABUSEIPDB API KEY",
        "user_id": "YOUR ABUSEIPDB USER ID",
    }
    ```
3. Make sure the binary file executable

    ```bash
    $ chmod u+x /YOUR_PATH/abuseipdb-cli/bin/abuseipdb
    ```

4. To use it more easily from shell, you could deploy the bin file to `/usr/local/bin/` (need root or administrator permissions)

    ```bash
    # ln -s  /YOUR_PATH/abuseipdb-cli/bin/abuseipdb  /usr/local/bin/
    ```

    Otherwise, replace `abuseipdb` with `./YOUR_PATH_WHERE_YOU_STORE_THIS_PROJECT/bin/abuseipdb` in the following examples.


Documentation
-------------

## 1. Usage
```
 SYNOPSIS:
     abuseipdb -C ip [-d days]
     abuseipdb -R ip -c categories -m message

 OPTIONS:

   -h, --help
       Prints the current help. If given, all next arguments are ignored.

   -g, --config
       Prints the current config. If given, all next arguments are ignored.

   -l, --list
       Prints the list report categories. If given, all next arguments are ignored.

   -C, --check ip
       Performs a check request for the given IP adress. A valid IPv4 or IPv6 address is required.

   -d, --days days
       For a check request, defines the maxAgeDays. Min is 1, max is 365, default is 30.

   -R, --report ip
       Performs a report request for the given IP adress. A valid IPv4 or IPv6 address is required.

   -c, --categories categories
       For a report request, defines the report category(ies). Categories must be separate by a comma.
       Some catgeries cannot be used alone. A category can be represented by its shortname or by its
       id. Use abuseipdb -l to print the categories list.

   -m, --message message
       For a report request, defines the message to send with report. Message is required for all
       reports request.
```

You can print the help with:
```bash
abuseipdb -h
```
![help)](doc/help.png)

## 2. Report categories list
```
 |-----------------+-------+--------------------+-----------------|
 | ShortName       | Id    | Full name          | Can be alone?   |
 |-----------------+-------+--------------------+-----------------|
 | dns-c           |   1   | DNS Compromise     |       true      |
 | dns-p           |   2   | DNS Poisoning      |       true      |
 | fraud-orders    |   3   | Fraud Orders       |       true      |
 | ddos            |   4   | DDoS Attack        |       true      |
 | ftp-bf          |   5   | FTP Brute-Force    |       true      |
 | pingdeath       |   6   | Ping of Death      |       true      |
 | phishing        |   7   | Phishing           |       true      |
 | fraudvoip       |   8   | Fraud VoIP         |       true      |
 | openproxy       |   9   | Open Proxy         |       true      |
 | webspam         |   10  | Web Spam           |       true      |
 | emailspam       |   11  | Email Spam         |       true      |
 | blogspam        |   12  | Blog Spam          |       true      |
 | vpnip           |   13  | VPN IP             |      false      |
 | scan            |   14  | Port Scan          |       true      |
 | hack            |   15  | Hacking            |      false      |
 | sql             |   16  | SQL Injection      |       true      |
 | spoof           |   17  | Spoofing           |       true      |
 | brute           |   18  | Brute-Force        |       true      |
 | badbot          |   19  | Bad Web Bot        |       true      |
 | explhost        |   20  | Exploited Host     |       true      |
 | webattack       |   21  | Web App Attack     |       true      |
 | ssh             |   22  | SSH                |      false      |
 | oit             |   23  | IoT Targeted       |       true      |
 |----------------------------------------------------------------|
```

You can print the categories list with:
```bash
abuseipdb -l
```
![categories)](doc/categories.png)

## 3. Samples

>  As said on [abuseipdb](https://www.abuseipdb.com/check/127.0.0.1), ip `127.0.0.1` is a private IP address you can use for check/report api testing. Make sure you **do not** blacklist an internal IP on your server, otherwise you won't have a good day! 

Check the ip `127.0.0.1` (default is on last 30 days): 
```bash
abuseipdb -C 127.0.0.1 
```

Check the ip `127.0.0.1` in last 365 days: 
```bash
abuseipdb -R 127.0.0.1 -d 365
```


Report the ip `127.0.0.1` for `ssh` and `brute` with message `ssh brute force :(`: 
```bash
# with categories shortname
abuseipdb -R 127.0.0.1  -c ssh,brute  -m "ssh brute force :("

# or with categories id
abuseipdb -R 127.0.0.1  -c 22,18  -m "ssh brute force :("
```


License
-------

The MIT License (MIT)

Copyright (c) 2020 Kristuff

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
