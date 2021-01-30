# kristuff/abuseipdb-cli
> A CLI tool to check/report IP addresses with the AbuseIPDB API V2

[![Build Status](https://scrutinizer-ci.com/g/kristuff/abuseipdb-cli/badges/build.png?b=master)](https://scrutinizer-ci.com/g/kristuff/abuseipdb-cli/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kristuff/abuseipdb-cli/badges/quality-score.png?b=dev)](https://scrutinizer-ci.com/g/kristuff/abuseipdb-cli/?branch=master
[![Latest Stable Version](https://poser.pugx.org/kristuff/abuseipdb-cli/v/stable)](https://packagist.org/packages/kristuff/abuseipdb-cli)
[![License](https://poser.pugx.org/kristuff/abuseipdb-cli/license)](https://packagist.org/packages/kristuff/abuseipdb-cli)

[![sample-report](doc/sample-report.gif)](https://kristuff.fr/projects/abuseipdbcli)

Features
--------
- Single IP check request **✓** 
- IP block check request **✓** 
- Blacklist request **✓** 
- Single IP report request **✓** 
- Bulk report request (send `csv` file) **✓** 
- Clear IP address request (remove your own reports) **✓**
- Auto cleaning report comments from sensitive data (email, custom ip/domain names list)  **✓** 
- Output: colored reports, JSON or plaintext **✓** 
- Easy Fail2ban integration **✓** 

Requirements
------------
- PHP >= 7.1
- PHP's cURL  
- A valid [abuseipdb.com](https://abuseipdb.com) account with an API key
- Composer for install

Dependencies
------------
- [kristuff/abuseipdb](https://github.com/kristuff/abuseipdb) A wrapper for abuseIPDB API v2
- [kristuff/mishell](https://github.com/kristuff/mishell) Used to build cli colored/tables reports

More infos
----------
- [Project website](https://kristuff.fr/projects/abuseipdbcli)
- [Api documentation](https://kristuff.fr/projects/abuseipdbcli/doc)
- [Config/Install guide](https://kristuff.fr/projects/abuseipdbcli/technical#configuration)
- [Fail2ban integration](https://kristuff.fr/projects/abuseipdbcli/technical#fail2ban)

Screenshots
-----------

![sample-check-internal-ip](doc/sample-check-internal-ip.png)

![sample-checkblock-internal-ip](doc/sample-checkblock-internal-ip.png)

![sample-check-bad-ip](doc/sample-check-bad-ip.png)

![sample-checkblock-bad-ip](doc/sample-checkblock-bad-ip.png)

![sample-report-internal-ip](doc/sample-report-internal-ip.png)

![sample-sample-clear-internal-ip](doc/sample-clear-internal-ip.png)

![sample-blacklist](doc/sample-blacklist.png)

![sample-blacklist-plaintext](doc/sample-blacklist-plaintext.png)

License
-------

The MIT License (MIT)

Copyright (c) 2020-2021 Kristuff

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
