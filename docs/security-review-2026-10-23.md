# Security review — 23 October 2026

## Deployment findings

Read-only checks of `wotlk/grimguzzler-registration` returned PHP **8.3.31**.
The local infra Dockerfile targeted PHP 8.5, but that did not establish the
version actually serving requests. PHP's [21 October security advisory](https://www.php.net/)
identifies critical remote-code-execution vulnerabilities CVE-2026-109618 and
CVE-2026-109619, fixed in 8.3.36, 8.4.27 and 8.5.12 (and 8.2.35).

HEAD requests inside the live pod returned:

| Path | Observed status |
| --- | --- |
| `/` | 200 |
| `/.git/HEAD` | 200 |
| `/AGENT.md` | 200 |
| `/application/composer.json` | 403 |

No Git objects, credentials, player data or secret values were downloaded from
these HTTP paths. The successful metadata request establishes exposure, not
whether someone has previously accessed it. This review did not assess historical
compromise or the contents of Git history.

**Production has not been upgraded by this review.** Publishing the application
and infrastructure commits must be followed by an image build and GitOps rollout.

## Prepared changes

- Require patched PHP 8.4/8.5 and recommend 8.5.12. The local infra Dockerfile
  pins the verified official `php:8.5.12-apache` image at
  `sha256:e25ab14ef2cfec3bab9dd66c6cf871691146f428fbd72241b1a2c38fcacf92f5`.
- Refresh PHP dependencies and remove unused Requests and the legacy Google
  Authenticator dependency. Updated package APIs are exercised in compatibility
  tests. Composer reports no published advisories or abandoned dependencies.
- Add CSRF validation before application handlers, with hidden fields in all
  bundled themes. Reject array-shaped form/query fields before string operations.
- Use strict cookie-only sessions, HttpOnly/SameSite cookies, Secure cookies for
  configured HTTPS sites, no-store responses, no-referrer policy, and anti-framing
  headers. Ignore spoofable forwarded IP headers.
- Throttle portal login attempts across sessions, enforce game-account 2FA when
  readable base32 secrets are present, expire portal sessions after one hour, and
  invalidate them when stored credentials change. Unrecognized/encrypted 2FA
  secrets fail closed; integrating encrypted core-specific secrets needs a
  separate supported authentication interface.
- Replace predictable recovery/2FA tokens with 256-bit random tokens. Store a
  purpose-bound digest and timestamp, expire after one hour, and consume by an
  atomic database update to prevent replay. Old links are intentionally invalid.
- Make email links display confirmation forms, require CSRF-protected POST for
  changes, and allow users to choose their new password without emailing it.
  Remove remote QR-code generation that disclosed authenticator setup secrets.
- Send CAPTCHA verification as encoded POST data, with TLS verification and
  bounded timeouts. Limit SOAP connections and escape displayed messages.
- Deny public dotfiles/metadata, tests, docs, samples and directory listing;
  exclude repository metadata from builds. Keep application source root-owned
  and non-writable by the Apache worker.
- Update all 11 bundled jQuery full/slim copies to 3.7.1, removing the older
  versions affected by the [jQuery HTML parsing XSS issue](https://blog.jquery.com/2020/04/10/jquery-3-5-0-released/).
- Add weekly PHP dependency auditing, PHP 8.4/8.5 CI checks, and Dependabot
  configuration. Infra image smoke tests now check the PHP security floor,
  absence of `.git`, platform requirements, and dependency advisories.

## Validation completed

A disposable PHP 8.5.12 Apache pod was created without production config,
credentials, service-account token, or user traffic. It used dummy config and
SQLite for account tests; no real accounts, inventories or SOAP commands were
changed.

- Composer metadata validation and installed platform requirements passed.
- Updated lockfile audit returned `advisories: []` and `abandoned: []`.
- Syntax checks passed for all 78 PHP and PHP-sample files.
- 59 security/compatibility checks passed, including SRP6 credentials, RFC 6238,
  recovery expiry and atomic replay protection, credential-change session
  revocation, signup/invite rules, character ownership, login throttling across
  sessions, and current dependency APIs.
- 18 HTTP checks passed, including rendered player login, session cookie flags,
  security headers, forged checkout/logout rejection, valid logout, malformed
  input rejection, metadata denial, and confirmation-only recovery GET.
- Git diff checks, workflow YAML parsing, and every workflow shell-step syntax
  check passed.

### OS package scan

Trivy 0.76.1 scanned a snapshot of the isolated environment's Debian 13.7
package inventory against its current database. It reported 3 critical and 132
high package occurrences across 84 distinct CVEs, with no fixed versions listed
for those high/critical findings in that release. These are scanner findings,
not 135 independently demonstrated website exploits. The
[scan summary](security-os-scan-2026-10-23.json) preserves package names, versions,
status and tracker links.

The critical results include libxml2/libxml2-dev CVE-2026-6653 and linux-libc-dev
CVE-2026-93221. Debian confirms the installed libxml2 version is affected but
[classifies that issue as minor](https://security-tracker.debian.org/tracker/CVE-2026-6653).
The linux-libc-dev finding concerns installed development headers; it does not
identify the Kubernetes node's running kernel. That kernel was not audited.
Other build tools and packages are also present in this inventory. Runtime
reachability and vendor severity need triage before using these totals as a
production risk score.

**The OS scan is not clean.** Do not describe the candidate image or live site as
free of known vulnerabilities. Triage the remaining distro findings, reduce build
packages in the runtime image, track vendor fixes, and scan the exact built image.

The PHP 8.4 CI matrix is prepared but was not run locally. The full Docker image
build was not run because the local Docker daemon is unavailable. Equivalent
extensions and PHP/Apache hardening were exercised in the isolated official image.
Actual mail delivery, live registration, real SOAP service fulfillment/refunds,
and visual behavior of every theme were not exercised against production.

## Rollout

The runtime source of truth remains `homelab-infra`. Changes are made directly
in that repository's `ops/grimguzzler-registration/Dockerfile` and
`.github/workflows/grimguzzler-registration-image.yml`. Review and publish those
changes from the infra checkout alongside the application changes here.

1. Review and publish the app changes to the app ref used by
   `homelab-infra/ops/grimguzzler-registration/source.ref`.
2. Review and publish the infra Dockerfile/workflow changes. Build from the new
   app ref using the registration image workflow, and require the new smoke tests.
3. Review the generated deployment image update. Preserve image-digest pinning
   and the mounted configuration/catalog; do not edit the running pod's app files.
4. Deploy through GitOps. The current one-replica deployment allows zero surge,
   so the rollout briefly interrupts the website.
5. Verify live PHP reports 8.5.12+, the home page and login render, metadata URLs
   return 403/404, CSRF checks work, and a permitted test account can register,
   recover a password and use the portal. Check SOAP with a deliberate test order.
6. Monitor errors. Roll forward or roll back to a known-good **patched** image if
   application behavior regresses; PHP 8.3.31 is not a safe long-term rollback.

## Remaining work and limits

- Migrate the active theme to supported Bootstrap, then retire or migrate the
  other legacy themes and review remaining vendored browser libraries. Bootstrap
  itself marks [version 4 as end of life](https://getbootstrap.com/docs/4.6/getting-started/introduction/).
- Scan the exact final image, including OS packages and compiled components,
  before production rollout. Dependency auditing alone cannot certify it.
- Review exposure history for `/.git/` and assess any secrets ever committed to
  repository history; mounted Kubernetes secrets were not inspected or exposed
  by this review.
- Review the registration workload's shared game database/SOAP permissions and
  network access separately. This review did not change those access boundaries.
- No finite code review or scanner result can establish that no vulnerabilities
  exist. Re-run audits and update runtime images as advisories are published.
