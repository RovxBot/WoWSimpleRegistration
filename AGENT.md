# AGENT.md

This file exists to keep future agents from re-discovering the same deployment and gameplay constraints.

## Purpose

This repository powers the `grimguzzler-registration` website for a WotLK AzerothCore server.

The website is not a standalone source of truth at runtime. The live deployment is controlled by GitOps in a separate repo, and the app reads a mounted `config.php` plus a mounted service catalog from Kubernetes.

## Repositories

- App repo: `/home/Rov/Documents/Git/WoWSimpleRegistration`
- Infra repo: `/home/Rov/Documents/Git/homelab-infra`
- App repo HEAD when this file was written: `ee70f0b`
- Infra repo HEAD when this file was written: `99cb631`

## Source Of Truth

- Treat `homelab-infra` as the runtime source of truth for deployment wiring.
- Do not assume `application/config/config.php.sample` reflects live behavior.
- The live pod mounts `/var/www/html/application/config/config.php` from a ConfigMap.
- The live pod also mounts `/var/www/html/application/config/store-services.php` from a second ConfigMap.
- The image is built from this repo and then deployed by the infra repo.

## Live Deployment Snapshot

Verified against the live cluster on `2026-05-03`.

- Kubernetes namespace: `wotlk`
- Deployment: `grimguzzler-registration`
- Template in use: `advance`
- Server core: AzerothCore
- Auth DB: `acore_auth`
- Character DB: `acore_characters`
- MariaDB service: `wotlk-mariadb.wotlk.svc.cluster.local`
- SOAP service host: `wotlk-worldserver-soap.wotlk.svc.cluster.local`
- Realm ID: `1`
- Realm name: `Grim Guzzler`
- Expansion: `2` (`3.3.5a / 12340`)
- SRP6 support: enabled
- SRP6 version: `2`
- Battle.net support: not enabled in the live deployment at the time of verification
- Invite secret reference: secret `wotlk-accountmgr-invite`, key `INVITE_CODE`
- Signup toggle env: `ALLOW_SIGNUP`

Anything date-sensitive should be re-verified before changing behavior.

## Critical Gameplay Rules

- Cash shop currency is `Grim Token`.
- Grim Token is not stored in `account.votePoints`.
- Grim Token is a physical inventory item stored in characters' bags.
- Grim Token item entry is `90000`.
- The live token item ID was confirmed from `mod_grimtokendailies.conf` and from the database.
- Registration invite gating uses one shared secret invite code, not a pool of one-time codes.

## Runtime Config Contract

The mounted `config.php` is expected to provide these keys for the custom flow:

- `allow_signup`
- `invite_code`
- `store_enabled`
- `store_token_item_id`
- `store_catalog_file`

The corresponding GitOps env vars are:

- `ALLOW_SIGNUP`
- `INVITE_CODE`
- `STORE_ENABLED`
- `STORE_TOKEN_ITEM_ID`
- `STORE_CATALOG_FILE`

## Related Infra Files

When changing runtime behavior, inspect these files in `homelab-infra`:

- `apps/grimguzzler-registration/config-configmap.yaml`
- `apps/grimguzzler-registration/store-services-configmap.yaml`
- `apps/grimguzzler-registration/deployment.yaml`
- `apps/grimguzzler-registration/kustomization.yaml`
- `.github/workflows/grimguzzler-registration-image.yml`
- `ops/grimguzzler-registration/Dockerfile`
- `clusters/home/apps/grimguzzler-registration`

## App Files That Matter Most

When changing functionality in this repo, inspect these files first:

- `application/include/user.php`
- `application/include/store.php`
- `application/include/functions.php`
- `application/loader.php`
- `index.php`
- `application/config/config.php.sample`
- `application/config/store-services.php.sample`
- `template/advance/tpl/main.php`
- `template/advance/tpl/header.php`
- `template/advance/assets/css/style.css`
- `application/language/english.php`

## Registration Rules

- `user::is_signup_allowed()` controls whether new registration is open.
- If signup is closed, backend registration must still reject requests even if someone posts directly.
- `user::registration_requires_invite()` checks whether a shared invite code is configured.
- `user::validate_registration_invite_code()` compares the submitted code to the configured secret with `hash_equals`.
- There is no DB-backed invite code tracking anymore.
- There is no one-time invite consumption anymore.

## Player Portal And Grim Token Shop

The player portal is implemented in `application/include/store.php`.

- Login uses the same game account credentials as AzerothCore auth.
- Session keys used by the portal:
  - `portal_account_id`
  - `portal_username`
  - `portal_email`
- The portal is gated behind `store_enabled`.
- The `advance` theme is the only theme currently wired for the custom shop UX.

## Grim Token Balance Model

- The balance shown in the portal is an account-wide total.
- The total is computed by summing Grim Token stacks across all characters on the account.
- The query joins:
  - `characters`
  - `character_inventory`
  - `item_instance`
- Per-character token counts are also shown in the UI because they are useful for character-targeted services.

## Store Catalog Contract

The catalog is loaded from the mounted PHP file returned by `store_catalog_file`.

Each service entry must provide:

- `id`
- `title`
- `description`
- `price`
- `scope`
- `soap_command`

Supported `scope` values:

- `account`
- `character`

Supported placeholders in `soap_command`:

- `{ACCOUNT_ID}`
- `{ACCOUNT_USERNAME}`
- `{ACCOUNT_EMAIL}`
- `{TARGET_CHARACTER}`
- `{TARGET_GUID}`
- `{REALM_ID}`

If `scope` is `character`, the UI requires the player to choose one of their own characters.

## Verified SOAP-Safe Character Services

These commands were verified against AzerothCore worldserver command handling and are safe for the current v1 service catalog:

- `character rename {TARGET_CHARACTER}`
- `character customize {TARGET_CHARACTER}`
- `character changefaction {TARGET_CHARACTER}`
- `character changerace {TARGET_CHARACTER}`

Behavior notes:

- `character rename` sets the at-login rename flag.
- `character customize` sets the at-login customize flag.
- `character changefaction` sets the at-login faction change flag.
- `character changerace` sets the at-login race change flag.

## Current Catalog Prices

These prices were assumed during implementation because no existing source of truth for prices was found:

- Rename: `5`
- Customize: `10`
- Faction Change: `15`
- Race Change: `15`

If product requirements change, update:

- `apps/grimguzzler-registration/store-services-configmap.yaml`
- Optionally `application/config/store-services.php.sample`

## Order Tracking Table

The app creates this table idempotently in `acore_auth`:

- `store_service_orders`

It stores:

- account identity snapshot
- service snapshot
- character target snapshot
- token cost
- token spend ledger JSON
- SOAP command template and rendered snapshot
- status
- status message
- created, fulfilled, refunded, updated timestamps

## Checkout Flow

The intended checkout flow is:

1. Validate login.
2. Validate service ID.
3. Validate target character when required.
4. Block checkout if any character on the account is online.
5. Lock account processing with MySQL `GET_LOCK`.
6. Start a transaction in the character DB.
7. Lock the account's character rows and token rows.
8. Build a deterministic spend ledger.
9. Deduct Grim Tokens from inventory stacks.
10. Insert an order row in `acore_auth.store_service_orders`.
11. Commit the DB transaction.
12. Execute the SOAP command.
13. Mark the order `fulfilled` on success.
14. Refund and mark `failed_refunded` on definite SOAP failure.
15. Mark `unknown_needs_review` on ambiguous transport/timeout failures.

## Store Status Values

Current order statuses:

- `processing`
- `fulfilled`
- `failed_refunded`
- `unknown_needs_review`

## Refund Model

- Refunds use the saved token spend ledger.
- If the original item stack still exists, its count is incremented.
- If the original item stack was deleted, the app recreates both `item_instance` and `character_inventory`.
- The ledger preserves enough item metadata to restore deleted stacks.

## Important Guardrails

- Do not re-introduce `votePoints` as store currency.
- Do not re-introduce DB-backed invite code consumption unless requirements change explicitly.
- Do not treat `config.php.sample` as live runtime config.
- Do not print or commit secret values.
- Do not copy SOAP, DB, or invite secrets into notes, commits, PR text, or chat output.
- If you inspect pod env vars or secrets, summarize behavior without revealing literal secret values.

## Safe Verification Workflow

If local PHP is unavailable, lint files inside the live registration pod.

Discover the pod:

```bash
kubectl -n wotlk get pods -l app=grimguzzler-registration
```

Lint changed PHP files inside the pod without modifying the live app:

```bash
tar -cf - application/include/functions.php application/include/user.php application/include/store.php application/language/english.php application/config/config.php.sample application/config/store-services.php.sample template/advance/tpl/main.php template/advance/tpl/header.php | \
kubectl exec -i -n wotlk <pod-name> -c grimguzzler-registration -- sh -lc 'rm -rf /tmp/codex-verify && mkdir -p /tmp/codex-verify && tar -xf - -C /tmp/codex-verify && php -l /tmp/codex-verify/application/include/functions.php && php -l /tmp/codex-verify/application/include/user.php && php -l /tmp/codex-verify/application/include/store.php && php -l /tmp/codex-verify/application/language/english.php && php -l /tmp/codex-verify/application/config/config.php.sample && php -l /tmp/codex-verify/application/config/store-services.php.sample && php -l /tmp/codex-verify/template/advance/tpl/main.php && php -l /tmp/codex-verify/template/advance/tpl/header.php'
```

Validate the GitOps manifests:

```bash
kubectl kustomize /home/Rov/Documents/Git/homelab-infra/apps/grimguzzler-registration
```

Useful diff hygiene:

```bash
git diff --check
git status --short
```

## Safe Database Inspection Pattern

Use the live pod's existing env vars instead of printing credentials.

Example:

```bash
kubectl exec -n wotlk deploy/grimguzzler-registration -c grimguzzler-registration -- sh -lc 'mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" -N -e "SHOW DATABASES;"'
```

Use the same pattern to inspect `acore_auth` and `acore_characters`, but do not echo credentials or dump unrelated sensitive data.

## Things To Re-Verify Before Major Changes

- The live namespace and deployment name.
- The current runtime env values in the GitOps deployment.
- Whether Battle.net support is still disabled.
- Whether the token item ID is still `90000`.
- Whether service prices in the catalog have been changed.
- Whether the live deployment still mounts both `config.php` and `store-services.php`.

## If You Are Debugging A Failed Purchase

Check, in this order:

- Was any character on the account online at checkout time?
- Did the account have enough Grim Tokens across all characters?
- Did the order row get inserted into `store_service_orders`?
- What status was recorded?
- Was the SOAP result definite failure or ambiguous transport failure?
- If refunded, were `item_instance` and `character_inventory` restored correctly?
- Is the selected character on the same account and realm?

## If You Are Extending The Shop

- Prefer service-only SOAP-backed actions unless requirements change.
- Keep catalog definitions in GitOps, not in the auth DB.
- If you add a new placeholder, document it here and in the catalog sample.
- If you add non-`advance` theme support, document which templates are updated.
