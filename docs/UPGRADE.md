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

## Upgrading your MySQL/MariaDB version

This is independent of the OSPOS version upgrade above and only applies if you
want to move your database server to a newer MySQL/MariaDB major version
(for example 5.7 → 8.0). MySQL 8.4 support was added in OSPOS 3.4.3 (see the
[CHANGELOG](../CHANGELOG.md)); earlier 3.4 releases do not record MySQL 8
support, so make sure your OSPOS release supports the target version first.

> **Important:** if you are also upgrading OSPOS, do that **first** on your
> current MySQL version, then move the database server.

1. Stop application writes (take the POS offline or put the site in
   maintenance mode). They must stay stopped until the new server is active,
   otherwise transactions recorded after the final dump will be missing from
   the import.
2. Take the final backup of your database (a full `mysqldump`).
3. If you are upgrading in place:
   - **MySQL:** see the
     [MySQL in-place upgrade guide (5.7 → 8.0)](https://dev.mysql.com/blog-archive/inplace-upgrade-from-mysql-5-7-to-mysql-8-0/)
     or your distro's equivalent for your target version.
   - **MariaDB:** see the
     [MariaDB upgrade documentation](https://mariadb.com/kb/en/upgrading-mariadb/).
4. If you are moving to a fresh server instead:
    - Create the application database and the OSPOS database account on the
      new server and grant it access to that database (the same credentials
      your `.env` uses). A plain `mysqldump ospos` does not create the
      database, so it must exist before you import.
    - Import your dump into that database on the new server.
    - Point `.env` at the new server. Note that the `MYSQL_HOST_NAME`,
      `MYSQL_USERNAME`, `MYSQL_PASSWORD`, and `MYSQL_DB_NAME` environment
      variables, if set, override the corresponding `.env` values (see
      `app/Config/Database.php`), so make sure they are unset or updated to
      the new server.
5. Still with application writes stopped, log in and let OSPOS run its
   migrations if needed. Only resume writes once the migrations have completed
   and you have verified the app works against the new database.

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
