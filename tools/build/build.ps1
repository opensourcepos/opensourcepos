# ------------------------------------------------------
# Run this Powershell script to "build" OSPOS.
# The script moves to the repository root itself, so it can
# be invoked from any working directory.
# Use ".\tools\build\build.ps1" from the repository root (if you are in
# another directory, provide a valid path to this script)
# The leading ".\" tells Powershell that you trust it.
# ------------------------------------------------------

# Run from the repository root (two levels up from tools/build/)
Set-Location -Path (Split-Path -Parent (Split-Path -Parent $PSScriptRoot))

Write-Output "============================================================================="
Write-Output "Run Composer Install"
Write-Output "============================================================================="
composer install

Write-Output "============================================================================="
Write-Output "Run NPM Install"
Write-Output "============================================================================="
npm install

Write-Output "============================================================================="
Write-Output "Install the components needed to build OSPOS"
Write-Output "============================================================================="
npm run build

Write-Output "============================================================================="
Write-Output "Restore configured .env file if it exists."
Write-Output "(If one is found in a folder located at  ../env/<name-of-ospos-root-folder>)"
Write-Output "============================================================================="
$currentfolder = Split-Path -Path (Get-Location) -Leaf
if (Test-Path -Path ../env/$currentfolder/.env -PathType Leaf) {
Copy ../env/$currentfolder/.env
}
