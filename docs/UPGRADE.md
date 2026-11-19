# How to Upgrade

> [!NOTE]
> This guide covers upgrading an existing installation of OSPOS 3.x (for example
> 3.4.1 → 3.4.2, or any 3.4.x → a newer 3.4.x).
> Installing OSPOS for the first time is different — see [INSTALL.md](INSTALL.md).

## What is in the code, and what is not

Upgrading replaces the **application code** with the new release. Your data and
settings are **not** stored in the code. They live in:

- **Your database** — all of your business data (products, customers, sales,
  employees, settings, …).
- **Your `.env` file** (project root) — database credentials, `encryption.key`,
  `throttle.key`, `app.allowedHostnames`, and other configuration.
- **Your `public/uploads/` folder** — uploaded product images and other files.
- **Any custom `app/Config/*.php` edits** — only if you personally changed a
  config file.

Because of this, an upgrade is simply: **back up → replace the code → restore
your files → log in** (the database schema updates itself automatically — see
below). You do **not** need to run any SQL upgrade scripts by hand.

## What to back up before you start

1. **Your database** — a full dump (for example `mysqldump`) is the most
   important thing; it is the only place your data lives.
2. **Your `.env` file** (project root) — it holds your database password and
   your `encryption.key`. Losing `encryption.key` makes previously encrypted
   data unrecoverable.
3. **Your `public/uploads/` folder.**
4. **Your current code directory** — a full copy, so you can roll back.

## How to upgrade

1. **Back up** your database, your `.env` file, and your current code directory.
2. **Download** the new release (e.g. 3.4.2) from the
   [Releases page](https://github.com/opensourcepos/opensourcepos/releases) and
   unzip it.
3. **Replace the application code** with the files from the new release.
   (You can overwrite your existing install in place — see the next section.)
4. **Restore your user-specific files** on top of the new code:
   - Put your original `.env` back in the project root.
   - Put your original `public/uploads/` folder back in place.
   - Re-apply any custom `app/Config/*.php` edits you made.
5. **Log in.** The first login takes a little longer: OSPOS runs its database
   migrations automatically to bring your schema up to the new version.
6. That's it. Your data, settings, and uploads are intact and the app is on the
   new version.

## Can I just overwrite the new files on top of the old install?

Yes — uploading the new release on top of your existing install is fine, **as
long as you protect your user-specific files first.** Two things to watch:

> [!WARNING]
> Releases ship with a **fresh, default `.env` file** (generated from
> `.env.example`, pointing at `localhost` with the default `ospos` credentials).
> If you let the new release's `.env` replace yours, you lose your database
> password and `encryption.key`. **Copy your current `.env` out of the way before
> overwriting, and copy it back afterwards.** If you do end up starting from the
> new `.env`, copy your old `database.*` and `encryption.key` values into it.

> [!WARNING]
> Do **not** delete your database or `public/uploads/` folder during an upgrade —
> they are separate from the code and hold everything you care about.

## Docker-specific upgrade instructions

The bundled Docker setup runs `php spark env:provision` at container startup
(`docker-compose.yml` command), which ensures the encryption and throttle keys
are present and handles automatic migration of legacy secrets. See
[INSTALL.md#local-install-using-docker](INSTALL.md#local-install-using-docker)
for the full setup guide, including key configuration.

### Upgrading a Docker container

1. **Back up** your `.env` file (it holds your `encryption.key` and database
   password).
2. **Pull** the new image: `docker compose pull` (or edit the image tag and run
   `docker compose up`).
3. **Preserve** your existing `.env` file — do **not** overwrite it with
   `.env.example` or delete it.
4. **Restart** the stack: `docker compose up -d`.
5. **Verify** the migration output: run
   `docker compose logs | grep env:provision` and look for the green messages:
   ```
   encryption.key     : CI4 key already present
   ```
   or (if upgrading from a pre-CI4 version):
   ```
   encryption.key     : rotated CI3 -> CI4 key
   legacy secrets     : converted and verified to CI4 cipher
   ```

### Encryption key migration (pre-CI4 → CI4)

If your installation predates the CI4 migration (OSPOS 3.3 and earlier), the
encryption key handling in Docker was different from bare-metal setups. In the
pre-CI4 Docker image the application read the key from the `ENCRYPTION_KEY`
environment variable; since the old compose file did not set it, the key was
`''` on every start. The first time a user configured SMTP or SMS credentials,
the application auto-generated a random 32-byte key and wrote it into
`config.php` at runtime — this key was **ephemeral**, lost whenever the
container was recreated.

The `ospos_app_config` table stores the encrypted secrets
(`smtp_pass`, `mailchimp_api_key`, `mailchimp_list_id`, `clcdesq_api_key`,
`clcdesq_api_url`) but **never** the encryption key itself.

When upgrading to CI4, the `env:provision` command at container startup handles
the key as follows:

- **If `.env` (or the `ENCRYPTION_KEY` environment variable) contains a short
  key** (< 64 characters, the CI3 format): `env:provision` decrypts the legacy
  secrets, generates a new 64-character CI4 key (256-bit, `AES-256-CTR`),
  backs up `.env`, writes the new key, re-encrypts the secrets with the CI4
  cipher, verifies the round-trip, and persists the result.
- **If `.env` is empty or contains a new key** and there are legacy secrets in
  the database: `env:provision` generates a fresh CI4 key and outputs a warning
  that the old secrets cannot be recovered.

This means **the old CI3 key must be present in `.env` (or the `ENCRYPTION_KEY`
environment variable) before the upgrade for the migration to succeed**. If you
had previously set an `ENCRYPTION_KEY` in your old `docker-compose.yml` or
saved the short key somewhere, include it in your new `.env` or `docker-compose.yml`
before restarting. If you do not have the old key and your database contains
encrypted secrets (SMTP password, Mailchimp API key, etc.), those secrets will
be lost — you will need to re-enter them after the upgrade.

### After migration

Once the key has been rotated, your `.env` will contain a 64-character hex key
(e.g., `encryption.key='a1b2c3d4...'`). For Docker deployments, the recommended
approach going forward is to supply the key via the `ENCRYPTION_KEY` environment
variable (see [INSTALL.md#local-install-using-docker](INSTALL.md#local-install-using-docker))
instead of editing `.env` directly, which avoids the atomic write issue on bind
mounts.

If you supplied the key via `ENCRYPTION_KEY` in your `docker-compose.yml`
`environment` list, no further action is needed — the key stays in your shell
profile, Compose's `.env` file, or `env_file`, and the container receives it
on every start.

### Cannot access the UI after upgrading?

If the containers start but the web interface is blank or shows an error, set
`CI_ENVIRONMENT=development` in your `.env` (or in the `environment` section of
`docker-compose.yml`) and restart. This reveals the PHP error message, which you
can then search for in the GitHub issues. After upgrading, you should set it
back to `production`.

## Version-specific notes

Check the [CHANGELOG](../CHANGELOG.md) for your target version for the full list of
changes. A few things that can require action when upgrading:

### 3.4.2 and later (production)
- **`app.allowedHostnames` is required in production.** If it is not set, the
  application fails to start (host-header-injection protection). Add it to your
  `.env` (or set the `ALLOWED_HOSTNAMES` environment variable), e.g.:
  ```
  app.allowedHostnames='yourdomain.com,www.yourdomain.com'
  ```

## Rolling back

To roll back, restore your backed-up code directory. Because the upgrade applies
database migrations on first login, check whether the older release works with
the current schema — if it does not, restore your pre-upgrade database dump along
with the old code. Your `.env` is independent of the code and does not need to
change.

## Still having trouble?

- Read the [README](../README.md) and the
  [GitHub issues](https://github.com/opensourcepos/opensourcepos/issues) — a
  similar issue may already be reported and answered.
- Open a new issue with your OSPOS version, the version you upgraded from, and
  the exact error you see. If the page is blank, check your web server and PHP
  error logs (you can temporarily raise `logger.threshold` in your `.env` to
  capture more detail) and include the relevant lines. Avoid enabling
  `display_errors` on a public production site, as it can expose internal details.
