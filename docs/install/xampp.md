# XAMPP Installation (Windows/macOS, local testing only)

Community guide — for **local testing only**, not for production. See
[INSTALL.md](../INSTALL.md) for the supported production setups.

> **Note:** OSPOS requires PHP 8.2+. Make sure your XAMPP ships a
> supported PHP version.

## Installation steps

1. Download and install XAMPP with a supported PHP version from
   [apachefriends.org](https://www.apachefriends.org/download.html).
2. Launch the XAMPP Control Panel and start Apache and MySQL.
3. Enable the required PHP extensions in `php.ini`
   (XAMPP ships most of them; uncomment if missing):
   - `extension=gd`
   - `extension=intl`
   - `extension=bcmath`
   - `extension=mbstring`
   - `extension=curl`
   - `extension=xml`
   - `extension=mysqli`
4. Download the latest OSPOS release from
   [GitHub Releases](https://github.com/opensourcepos/opensourcepos/releases).
5. On Windows, right-click the downloaded zip, select **Properties** and click
   **Unblock** if present. Extract the archive.
6. Place the extracted folder into the `htdocs` directory.
7. Open phpMyAdmin (XAMPP Control Panel → MySQL → Admin) and create a database
   named `ospos` (or your preferred name).
8. Copy `.env.example` to `.env` and configure the database connection.
   `app.allowedHostnames` must also be set, otherwise the application fails
   to start (Host Header Injection protection):

   ```ini
   database.default.hostname = 'localhost'
   database.default.database = 'ospos'
   database.default.username = 'root'
   database.default.password = ''    # default XAMPP has no password
   app.allowedHostnames = 'localhost'
   ```

9. Browse to `http://localhost/<folder-name>/public` and log in with
   `admin` / `pointofsale`.

> **Note:** for current releases the database is created automatically on
> first run. You do not need to import a schema file manually.

## Troubleshooting

- **"system folder missing"**: you downloaded the source code — use a release
  build, or build the project (see [BUILD.md](../BUILD.md)).
- **Extensions not loading**: restart Apache after modifying `php.ini`.
- **WAMP users**: follow the same steps; make sure the ICU extension files are
  copied to the right locations for `intl` support.
