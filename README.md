# kristuff/abuse-ipdb
The CLI version of [kristuff/abuse-ipdb](https://github.com/kristuff/abuse-ipdb), a mini library to work with the AbuseIPDB api V2




Requirements
------------
- PHP >= 7.1
- PHP's cURL  
- A valid [abuseipdb.com](https://abuseipdb.com) account with an API key

Dependencies
------------
- [kristuff/abuse-ipdb](https://github.com/kristuff/abuse-ipdb) The library to communicate withe ABUSEipdb api V2
- [kristuff/mishell](https://github.com/kristuff/mishell) Used to build cli colored/tables reports

Install
-------

1. Install with composer

    ```bash
    mkdir abuseipdb-cli
    cd abuseipdb-cli
    composer require kristuff/abuseipdb-cli
    composer install
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

    Otherwise, replace `abuseipdb` with `./YOUR_PATH_WHERE_YOU_STORE_THIS_PROJECT/bin/abuseipdb` in the follonwing examples.


Documentation
-------------

## 1. Usage

You can print the help with:
```bash
abuseipdb -h
```


    ## SYNOPSIS:
    ```bash
    abuseipdb -C ip [-d days]
    abuseipdb -R ip -c categories -m message
    ```

    ## OPTIONS:
    ```
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
        id. Use abuse-ipdb -l to print the list

    -m, --message message
        For a report request, defines the message to send with report. Message is required for all
        reports request.
    ```

## 2. Report categories list

You can print the categories list with:
```bash
abuseipdb -l
```

```
 |---------------------------------------------|
 | shortName       | Id | Full name            |
 |-----------------+----+----------------------|
 | dns-c           | 1  | DNS Compromise       |
 | dns-p           | 2  | DNS Poisoning        |
 | fraud-orders    | 3  | Fraud Orders         |
 | ddos            | 4  | DDoS Attack          |
 | ftp-bf          | 5  | FTP Brute-Force      |
 | pingdeath       | 6  | Ping of Death        |
 | phishing        | 7  | Phishing             |
 | fraudvoip       | 8  | Fraud VoIP           |
 | openproxy       | 9  | Open Proxy           |
 | webspam         | 10 | Web Spam             |
 | emailspam       | 11 | Email Spam           |
 | blogspam        | 12 | Blog Spam            |
 | vpnip           | 13 | VPN IP               |
 | scan            | 14 | Port Scan            |
 | hack            | 15 | Hacking              |
 | sql             | 16 | SQL Injection        |
 | spoof           | 17 | Spoofing             |
 | brute           | 18 | Brute-Force          |
 | badbot          | 19 | Bad Web Bot          |
 | explhost        | 20 | Exploited Host       |
 | webattack       | 21 | Web App Attack       |
 | ssh             | 22 | SSH                  |
 | oit             | 23 | IoT Targeted         |
 |---------------------------------------------|
```

## 3. Samples

>  As said on [abuseipdb](https://www.abuseipdb.com/check/127.0.0.1), ip `127.0.0.1` is private IP address, you can use for api testing. Make sure you **do not** blacklist an internal IP, otherwise you won't have a good day! 

Check for ip `127.0.0.1` (default is on last 30 days): 
```bash
abuseipdb -C 127.0.0.1 
```

Check for ip `127.0.0.1` in last 365 days: 
```bash
abuseipdb -R 127.0.0.1 -d 365
```

Report ip `127.0.0.1` for `ssh` and `brute` with message `ssh brute force :(`: 
```bash
# with cat shortnames
abuseipdb -R 127.0.0.1  -c "ssh,brute"  -m "ssh brute force :("
# or with cat ids
abuseipdb -R 127.0.0.1  -c "22,18"  -m "ssh brute force :("
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
