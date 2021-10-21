## How to Upgrade

1. Back up all your current database and OSPOS code.
2. Make sure you have a copy of `application/config/config.php` and `application/config/database.php`.
3. Remove all directories.
4. Install the new OSPOS.
5. (Only applicable if upgrading from pre `3.0.0`) Run the database upgrade scripts from `database` dir (check which ones you need according to the version you are upgrading from).
6. Take the saved old `config.php` and upgrade the new `config.php` with any additional changes you made in the old.
   Take time to understand if new config rules require some changes (e.g. encryption keys).
7. Take the saved old `database.php` and change the new `database.php` to contain all the configurations you had in the old setup.
   Please try not to use the old layout, use the new one and copy the content of the config variables.
8. Restore the content of the old `uploads` folder into `public/uploads` one.
9. Once the new code is in place, the database is manually updated, and the config files are in place, you're good to go.
10. The first login will take longer because OSPOS post `3.0.0` will upgrade automatically to the latest version.
11. If everything went according to plan, you'll be able to use your upgraded version of OSPOS.
12. Still have issues? Please check the [README](README.md) and [GitHub issues](https://github.com/opensourcepos/opensourcepos/issues).
    Maybe a similar issue has already been reported, and you can find your answer there.
