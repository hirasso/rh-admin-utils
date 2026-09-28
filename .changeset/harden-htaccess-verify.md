---
"rh-admin-utils": patch
---

Roll back the .htaccess hardening if it breaks the site

Apache exposes no API for reading `AllowOverride`, so directives it doesn't permit
would take the whole site down with a 500. The home URL is now requested once
before and once after the directives are written, and the previous file is restored
verbatim if it starts erroring. The notice then names the reason instead of only
showing the directives.

The check is skipped when the site was already unreachable before writing, since
loopback requests are blocked on plenty of hosts and treating that as a failure
would mean those sites could never be hardened.
