# Security

Use the latest patched PHP 8.5 release. This revision accepts PHP 8.4.27+ in
the 8.4 branch and PHP 8.5.12+ in the 8.5 branch. These minimums include the
[21 October 2026 PHP security fixes](https://www.php.net/).
Check [PHP's supported versions](https://www.php.net/supported-versions.php)
and advisories before deploying; a minimum version check cannot keep a server
patched automatically.

Install dependencies from `application/composer.lock`, run `composer audit --locked`, and run the security workflow before deploying changes. The workflow
also runs weekly. Dependencies and GitHub Actions receive weekly Dependabot
update checks once these files are pushed to GitHub.

Keep `debug_mode` disabled in production, serve accounts over HTTPS, exclude Git
metadata from images, and deny public access to `application/`, dotfiles, tests,
and configuration samples. Apache deployments must allow the supplied access
rules. Other web servers need equivalent rules.

Recovery and 2FA confirmation links expire after one hour and work once. Older
links issued before this change must be requested again. New passwords are
chosen on the confirmation form; notification emails do not include passwords.

The login limiter uses a bounded local temporary directory. It is suitable for
the current single-replica deployment. Multiple replicas need a shared limiter
or enforced proxy limits. Forwarded IP headers are not trusted by the app.

The bundled Bootstrap 3/4 themes are end of life. Upgrading PHP and passing a
Composer audit does not resolve that maintenance risk or certify that a site
has no vulnerabilities. See the [security review](docs/security-review-2026-10-23.md)
for observed deployment findings, validation, and remaining work.

Report suspected vulnerabilities privately to the repository owner, using
GitHub's private vulnerability reporting when it is enabled. Do not post
credentials, recovery tokens, player information, or working exploits in public
issues.
