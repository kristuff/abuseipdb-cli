% ABUSEIPDB(1) Abuseipdb Client User Manuals
% Kristuff 
% December 7, 2021

# NAME

abuseipdb - check, report IP addresses download blacklist with AbuseIPDB API v2.

# SYNOPSIS

abuseipdb COMMANDE [*OPTIONS*]...

# DESCRIPTION

**abuseipdb** is a client for AbuseIPDB API v2. You can use it to check IP addresses or subnet, 
report IP addressess, clear your report for a given IP, or download blacklist. 

# COMMANDES

-h, \--help
:   Displays a short-help text and exits.

-G, \--config
:   Displays the current config and exits.

-L, \--list
:   Displays the list report categories and exits.

-C *IP*, \--check *IP*
:   Performs a check request for the given IP address. A valid IPv4 or IPv6 address is required.

-R *IP*, \--report *IP*
:   Performs a report request for the given IP address. A valid IPv4 or IPv6 address is required.

-V *FILE*, \--bulk-report FILE
:   Performs a bulk-report request sending a csv file. A valid file name or full path is required.

-E *IP*, \--clear *IP*
:   Remove own reports for the given IP address. A valid IPv4 or IPv6 address is required.

-K *NETWORK*, \--checkblock *NETWORK*
:   Performs a check-block request for the given network. A valid subnet (v4 or v6) denoted with 
    CIDR notation is required.

-B, \--blacklist
:   Performs a blacklist request: get a list of reported IPs.

\---version
:   Prints the current version.


# OPTIONS

-d *DAYS*, \--days *DAYS*
:   For a check or check-block request, defines the maxAgeDays. Min is 1, max is 365, default is 30.

-c *CATEGORIES*, \--categories *CATEGORIES*
:   For a report request, defines the report category(ies). Categories must be separate by a comma. 
    Some catgeries cannot be used alone. A category can be represented by its shortname or by its id. 
    Use abuseipdb -L to print the categories list.

-m *MESSAGE*, \--message *MESSAGE*
:   For a report request, defines the message to send with report. Message is required for all report 
    requests.

-l *LIMIT*, \--limit *LIMIT*
:   For a blacklist request, defines the limit (default is 1000). For a check request with verbose flag, 
    sets the max number of last reports displayed (default is 10). For a check-block request, sets the 
    max number of IPs displayed (default is 0 mean no limit).

-o *FORMAT*, \--output *FORMAT*
:   Defines the output format for API requests. Default is a colorized report, possible formats are 
    json or plaintext. Plaintext option prints partial response (blacklist: IPs list, check or report: 
    confidence score only, check-block: reported IP list with confidence score, bulk-report: 
    number of saved reports, clear: number of deleted reports).

-s *SCORE*, \--score *SCORE*
:   For a blacklist request, sets the confidence score minimum. The confidence minimum must be between 
    25 and 100. This parameter is subscriber feature (not honored otherwise, allways 100).

-t *TIMEOUT*, \--timeout *TIMEOUT*
:   Define the timeout in API request and overwrite the value defined in conf.ini or local.ini.
    Timeout is expressed in milliseconds.

-v, \--verbose
:   For a check request, display additional fields like the x last reports. Max number of last reports 
    is defined in config. This increases request time and response size.

# BUGS

Submit bug reports online at: <https://github.com/kristuff/abuseipdb-cli/issues>

# SEE ALSO

Source code at: <https://github.com/kristuff/abuseipdb-cli>

Full documentation at: <https://kristuff.fr/projects/abuseipdbcli/doc>

