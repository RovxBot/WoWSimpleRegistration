# Grim Guzzler website redesign — 23 October 2026

The `advance` theme is a multi-page site using the supplied launcher logo, Play and Store graphics, and tavern scene. WebP copies preserve the original artwork and transparency; the launcher originals are unchanged.

## Pages

The site uses existing PHP hosting with no rewrite or frontend build requirement:

- `/index.php`: compact home dashboard with realm status, recent victories, realm information, and links to rankings.
- `?page=status`: heartbeat status, reported uptime and session peak, faction population, and online roster.
- `?page=arena`: separate 2v2, 3v3, and 5v5 ladders with ratings, captains, season records, and win percentages.
- `?page=raids`: Classic, TBC, and Wrath tabs with boss victories and final-boss clears, filtered by raid, supported size/difficulty, and 7/30/90-day history.
- `?page=guilds`: raid progression, lifetime PvP, and roster-size rankings.
- `?page=pvp`: paginated honorable-kill leaderboard with faction, class, and name filters.
- `?page=play`, `?page=register`, `?page=account`, and `?page=support`: connection help, registration and password tools, account/shop, and rules/FAQ/support.

Rankings include players and playerbots, as requested. Direct links, browser history, and GET filters work without JavaScript. Legacy section links redirect to their corresponding pages. Unknown pages and unconfigured realms return HTTP 404.

## Data and ranking rules

`application/include/realm-stats.php` reads existing AzerothCore tables without schema changes. Live read-only schema checks verified all seven query shapes. Arena teams, guilds, and PvP totals exist; `log_encounter` contained zero rows when checked. The raid pages show empty states until encounter records exist. Classic/TBC collection requires the worldserver image change described below.

- Realm state follows `realmlist.flag` and the newest `uptime` heartbeat. By default, a heartbeat older than 20 minutes is offline. Missing data remains unknown/unavailable. Status is a reported heartbeat, not a direct connectivity probe.
- Raid data comes from `log_encounter`, not inferred character achievements. Historical activity before logging cannot be reconstructed. The catalog covers Molten Core, Blackwing Lair, Zul'Gurub, both Ahn'Qiraj raids, the restored Classic versions of Onyxia/Naxxramas, all nine TBC raids, and the existing Wrath raids. Scripted credits include Karazhan's Opera/Chess events, the Gunship Battle, and Valithria. Only cataloged credits and supported modes contribute to rankings; non-canonical credits cannot inflate progression.
- Classic raids use their 20/40-player labels; Karazhan and Zul'Aman use 10, and the other TBC raids use 25. Shared-map Classic records stay separate from Wrath records. Classic Naxxramas requires a module providing the restored 40-player version; the currently configured `mod-progression-system` does not provide it. The site supports those records without installing or enabling that game content.
- A clear means a final-boss victory. The log has no instance identifier, so the site does not claim to verify every boss in one lockout. Vault of Archavon tracks its independent bosses without inferring clears.
- Guild progression counts distinct boss/difficulty combinations. Guild credit requires 80% of the recorded participants to belong to that guild at the time of the encounter. Current guild members provide lifetime PvP totals.
- Raid rankings use up to the latest 1,000 encounters within the selected period; a visible notice appears if that limit is reached. Recent lists remain separate per raid/difficulty.
- Raw encounter logs contain private account and network details. They are parsed internally and discarded. Only an explicit public-field subset is returned and cached. Public snapshots are cached for 30 seconds; arbitrary PvP searches create no disk cache files.

Optional configuration keys are `realm_stats_cache_ttl` (default 30), `realm_stats_cache_path` (default private temporary directory), and `realm_heartbeat_grace` (default 1200 seconds). Mounted configurations can omit these keys.

## Account and deployment compatibility

Registration, shared invitation checks, authentication, CAPTCHA, token inventory, service catalogs, SOAP checkout, and order handling retain their existing contracts. Forms return to their dedicated pages; old POST targets are routed to the appropriate account or registration page. Feature visibility switches and Battle.net identity fields remain supported. Password-reset and 2FA confirmations use the same branding.

No production source was uploaded and no account actions or purchases were executed. The website still deploys through its existing image-build and GitOps workflow, including its patched PHP requirement. Nothing has been committed, pushed, built into a production image, or deployed.

## Classic and TBC collection

The upstream core's `Map::LogEncounterFinished` explicitly rejects maps with expansion below 2. Adding website catalog entries alone would leave Classic/TBC raid logs empty. The local `homelab-infra` checkout now contains:

- `apps/wotlk/patches/raid-logging-all-expansions.patch`: removes that expansion restriction while retaining the raid and instance guards. Progression-system's level-63 Onyxia is tagged as log difficulty `2`, reserving normal modes `0`/`1` for the later Wrath version. Restored Onyxia/Naxx40 instances using mode `2` resolve their encounter definitions through the normal encounter list while retaining their distinct log mode.
- `.github/workflows/azerothcore-wotlk-images.yml`: checks and applies the patch to the cloned core before compiling. An incompatible upstream change fails the image build.

The core patch preserves the existing log schema and roster format. No live logger or database settings were changed. Rebuild and deploy the worldserver through that workflow to start collecting future Classic/TBC encounters, alongside deployment of the website image. Existing unlogged kills cannot be backfilled from this table. Restoring Classic Naxxramas itself is separate from website support and is not performed by these changes.

## Validation

- All 92 PHP/sample files parsed in a temporary local PHP 8.4 WebAssembly runtime (8.4.25). This is syntax/fixture validation, not validation of the production PHP security floor.
- 59 isolated database checks cover status freshness, faction counts, bracket separation, filtering and pagination, parameter binding, guild credit across expansions, restored raid mode separation, final bosses, invalid credits/modes, bounded histories, sanitized caches, disabled views, and unavailable sources. Run with `php tests/realm-stats.php`; it is also included in `composer test`.
- 1,429 local page checks passed across 63 requests, including all routes, every Classic/TBC raid view, expansion defaults, raid sizes, ranking filters, account states, CSRF fields, form destinations, labels, anchors, artwork, and 404 responses.
- Seven SELECT query shapes validated against live schemas without returning private encounter data or writing to the database.
- Browser QA covered desktop/mobile navigation, ranking tables, arena brackets, PvP search, raid filters, realmlist copying, and account dialogs.
- The collection patch applies cleanly to both local AzerothCore checkouts. Workflow YAML, shell syntax, and patch order passed checks. An isolated C++17 smoke test of the changed selection/guard/mode code passed; the full worldserver image was not compiled or run.
- JavaScript syntax and Git whitespace checks passed. The full native backend security/HTTP suite was not run; its HTTP test was updated for the separate account page.

Visual previews use isolated fixture data. They do not depict the live realm's current population or rankings.
