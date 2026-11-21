## Docker-specific upgrade instructions

The bundled Docker setup runs `php spark env:provision` at container startup
(`docker-compose.yml` command), which ensures the encryption and throttle keys
are present and handles automatic migration of legacy secrets. See
[INSTALL.md#local-install-using-docker](INSTALL.md#local-install-using-docker)
for the full setup guide, including key configuration.

> [!CAUTION]
> **Preserve your legacy `ENCRYPTION_KEY` before restarting.** If your old
> pre-CI4 compose file set `ENCRYPTION_KEY`, that value is required to decrypt
> stored CI3 secrets. The old compose file did not set this variable by default,
> so if you never configured it, no encryption was performed and no migration
> is needed. If you did set it, the key is not stored in the database and cannot
> be recovered from the container — you must supply the same value during
> migration.

### Upgrading a Docker container

1. **Preserve the legacy `ENCRYPTION_KEY`** (see the caution above) if you
   manually set one in your old compose file. If you never set `ENCRYPTION_KEY`,
   no encryption was performed and you can skip the migration steps below.
2. **Back up** your `.env` file (it holds your `encryption.key` and database
   password).
3. **Pull** the new image: `docker compose pull` (or edit the image tag and run
   `docker compose up`).
4. **Preserve** your existing `.env` file — do **not** overwrite it with
   `.env.example` or delete it.
5. **Restart** the stack: `docker compose up -d`.
6. **Verify the migration:** show the full service logs (`docker compose logs`)
   and look for the green messages. If your compose file includes the
   `env:provision` command, check for:
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
`ospos_app_config` table stores encrypted secrets (`smtp_pass`,
`mailchimp_api_key`, `mailchimp_list_id`, `clcdesq_api_key`, `clcdesq_api_url`)
but **never** the encryption key itself.

Your legacy `ENCRYPTION_KEY` value must be present either in `.env` (as
`encryption.key=`) or as the `ENCRYPTION_KEY` environment variable in your new
`docker-compose.yml`. Without it, the old encrypted secrets cannot be decrypted
and will be lost.

The `env:provision` command at container startup handles the key as follows:

- **If `.env` (or `ENCRYPTION_KEY`) contains a short key** (< 64 characters,
  the CI3 format): `env:provision` decrypts the legacy secrets, generates a new
  64-character CI4 key (256-bit, `AES-256-CTR`), backs up `.env`, writes the
  new key, re-encrypts the secrets, verifies the round-trip, and persists.
- **If `.env` (or `ENCRYPTION_KEY`) already contains a 64-character key** (a
  valid CI4 key): `env:provision` prints `encryption.key: CI4 key already
  present` and exits. It does **not** check for or migrate remaining CI3 secrets.
  If your database still contains CI3-encrypted secrets, preserve the legacy key
  and verify that the conversion was performed (e.g. check your old `.env` backup
  or container config); provisioning cannot retroactively convert them.
- **If `.env` is empty or contains a new key** and there are legacy secrets in
  the database: `env:provision` generates a fresh CI4 key and outputs a warning
  that the old secrets cannot be recovered.

> [!WARNING]
> **Rotation requires `.env` to be replaceable by rename.** The bundled
> `docker-compose.yml` mounts `./.env` as a single file (`create_host_path:
> false`). On Linux, `rename()` cannot replace a mount point, so rotating a
> pre-CI4 key via `.env` will **fail**, `env:provision` exits non-zero, and
> Apache does not start (the `&&` in the compose `command`).
>
> **To upgrade from pre-CI4 with the bundled compose file, supply the legacy
> key via the `ENCRYPTION_KEY` environment variable** instead of `.env`:
> ```yaml
> environment:
>     - ENCRYPTION_KEY=<your-ci3-short-key>
> ```
> Note that `env:provision` still rewrites `.env` for a short key, so the
> single‑file mount risk remains. If the rotation write fails, `env:provision`
> exits non-zero and Apache does not start. In that case, stop the container and
> switch to a mountable `.env` directory (a volume or bind mount pointing at a
> directory containing the `.env` file) instead of retrying — removing the
> `ENCRYPTION_KEY` variable and keeping the single‑file mount loses the legacy
> key, and provisioning will generate a new key without the old secrets. After
> the initial key migration, keep the rotated key unchanged; generating a
> different key without re-encrypting the secrets makes them unreadable.

### After migration

Once the key has been rotated, your `.env` will contain a 64-character hex key
(e.g., `encryption.key='a1b2c3d4...'`). For Docker deployments, the recommended
approach going forward is to supply the key via the `ENCRYPTION_KEY` environment
variable (see [INSTALL.md#local-install-using-docker](INSTALL.md#local-install-using-docker))
instead of editing `.env` directly, which avoids the atomic write issue on bind
mounts.

If you used `ENCRYPTION_KEY` to supply the old short key and `env:provision`
rotated it, the new 64-character key is written to `.env` but the Compose
environment variable still holds the old key. **Securely copy the rotated key
from `.env` into your external configuration** (shell profile, Compose-side
`.env`, or `env_file`) and update `ENCRYPTION_KEY` to the new value.

### Cannot access the UI after upgrading?

If the containers start but the web interface is blank or shows an error, **restrict
access to the installation first** (e.g. firewall rules or a maintenance page),
then set `CI_ENVIRONMENT=development` in your `.env` (or in the `environment`
section of `docker-compose.yml`) and restart. This reveals the PHP error message,
which you can then search for in the GitHub issues. After upgrading, set it back
to `production` and restore access.
