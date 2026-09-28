---
"rh-admin-utils": patch
---

Fix two security issues in the ACF oEmbed whitelist

The AJAX handler ran before ACF's own nonce check, so any logged-in user could
reach it and make the site fetch an arbitrary URL. It now verifies the nonce the
same way ACF does before doing anything else.

The whitelist was also matched against the oEmbed *response body* rather than the
URL, so any page whose markup happened to contain an allowed host passed. Hosts
are now parsed from the URL and compared exactly (subdomains included), before any
HTTP request is made.
