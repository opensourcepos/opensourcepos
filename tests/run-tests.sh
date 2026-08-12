#!/bin/bash
set -e
cd /app

# The test suite uses a dedicated database (ospos_test) so the live ospos
# data is never touched. This database is recreated on every run because the
# OSPOS migrations do not round-trip cleanly through CI4's rollback path
# (CI only works because it starts from an empty database).
export CI_ENVIRONMENT=testing
export MYSQL_DB_NAME="${MYSQL_TEST_DB_NAME:-ospos_test}"

php -r '
$db = getenv("MYSQL_DB_NAME");
$host = getenv("MYSQL_HOST_NAME") ?: "mysql";
$user = getenv("MYSQL_USERNAME") ?: "admin";
$pass = getenv("MYSQL_PASSWORD") ?: "pointofsale";
$c = new mysqli($host, $user, $pass);
if ($c->connect_errno) {
    fwrite(STDERR, "Database connection failed: " . $c->connect_error . "\n");
    exit(1);
}
$c->query("DROP DATABASE IF EXISTS `$db`") or die("DROP failed: " . $c->error . "\n");
$c->query("CREATE DATABASE `$db`") or die("CREATE failed: " . $c->error . "\n");
echo "Recreated test database `$db`.\n";
'

exec php vendor/bin/phpunit --no-coverage -c /app/phpunit.xml "$@"
