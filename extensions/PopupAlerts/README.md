# PopupAlerts

The extension allows adding popup alerts to wiki pages with an
optional expiration date to allow controlling if and when
users will see the popup again. It also has an integrated checksum-based
invalidation mechanism to detect popup content change and
hence reset the "already-seen" markers.

# Requirements

* Mediawiki 1.30+

# Installation

* Add `wfLoadExtension('PopupAlerts');` to the bottom of your `LocalSettings.php` file

# Usage

Just add the following parser function to the page markup:

```
{{#popupalert:some random wiki markup}} - popup will be seen once in 100 years
{{#popupalert:some random wiki markup|3600}} - popup will be seen once in one hour (3600 seconds)
```

**Note**: if you have many popups added to the page only the first one will be
taken into account. This limitation was made intentionally to prevent wiki pages from
being spammed with popups.
