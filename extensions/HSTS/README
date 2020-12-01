The HSTS extension implements the HTTP Strict Transport Security feature (RFC 6797) as an opt-in (or opt-out) preference for each user, in order to be always redirected to the HTTPS version of the website, if the user agent (client browser) understands the HSTS functionality. The server administrator is also given the possibility to force the anonymous and/or logged-in users to have a STS header and thus stay on HTTPS.

== Configuration ==

These variables may be overridden in LocalSettings.php after you include the
extension file.

* <code>$wgHSTSBetaFeature</code>: (boolean; default=false) activate the HSTS preference as a [[Extension:BetaFeatures|Beta Feature]] or as a classical user preference;
* <code>$wgHSTSForAnons</code>: (boolean; default=false) whether to give the STS header to anonymous users;
* <code>$wgHSTSForUsers</code>: (boolean; default=false) whether to force the STS header for logged-in users; if true, the users do no more have their preference available since it became unuseful due to the server adminstrator’s decision.
* <code>$wgHSTSIncludeSubdomains</code>: (boolean; default=false) whether to include the "includeSubDomains" keyword in the STS header.
* <code>$wgHSTSMaxAge</code>: (integer or string; default=30*86400=30 days) max-age parameter for HSTS; can be either:
** an integer: (e.g. 3600) fixed number of seconds before expiration of HSTS (note that 0 will deactivate HSTS the next time the user visit the website), or
** a date: (e.g. "2014-09-24T00:00:00Z") when HSTS will expire (e.g. just before certificate expiration); MediaWiki must understand the date (see [[Manual:WfTimestamp#Formats|the manual]]).<br />Note that in this second case the header is dynamical, so you may want to configure accordingly your cache servers for a consistent user experience, particularly given the authoritative HSTS header is the last sent, even if shorter.

Additionally, you can set up:
* <code>$wgDefaultUserOptions['hsts']</code>: (0 or 1; default=0) default value of the preference for logged-in users (<code>0</code> = opt-out or <code>1</code> = opt-in)

== Links ==

* [https://www.mediawiki.org/wiki/Extension:HSTS Main page of the extension]
* [https://www.mediawiki.org/wiki/Extension:BetaFeatures Extension:BetaFeatures]
