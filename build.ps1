# ------------------------------------------------------
# Run this Powershell script to "build" OSPOS.
# Execute this script from a terminal starting
# with the project root as the working directory.
# Use ".\build.ps1"
# The leading ".\" tells Powershell that you trust it.
# ------------------------------------------------------

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
if(Test-Path -Path ../env/$currentfolder/.env -PathType Leaf)
{
Copy ../env/$currentfolder/.env
}
