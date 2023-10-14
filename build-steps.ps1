# ----------------------------------------------------------------
# Run this Powershell script to "build" OSPOS (one step at a time).
# Execute this script from a terminal starting
# with the project root as the working directory.
# Use ".\build-steps.ps1"
# The leading ".\" tells Powershell that you trust it.
# ----------------------------------------------------------------

Write-Output "============================================================================="
Write-Output "1. Run Composer Install and npm install"
Write-Output "============================================================================="

composer install
npm install

Write-Output "============================================================================="
Write-Output "2. Run gulp clean "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

npm run gulp clean

Write-Output "============================================================================="
Write-Output "3. Install version 3 of bootswatch "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

npm run gulp copy-bootswatch

Write-Output "============================================================================="
Write-Output "4. Install version 5 of bootswatch "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

npm run gulp copy-bootswatch5

Write-Output "============================================================================="
Write-Output "5. Install the developer mode javascript "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

npm run gulp debug-js

Write-Output "============================================================================="
Write-Output "6. Install the production mode javascript "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

npm run gulp prod-js

Write-Output "============================================================================="
Write-Output "7. Install the developer mode css "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

npm run gulp debug-css

Write-Output "============================================================================="
Write-Output "8. Install the production mode css "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

npm run gulp prod-css

Write-Output "============================================================================="
Write-Output "9. Install fonts required by vendor utilities "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

npm run gulp copy-fonts

Write-Output "============================================================================="
Write-Output "10. Inject the required stylesheet and script into the login page "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

npm run gulp inject-login

Write-Output "============================================================================="
Write-Output "11. Restore configured .env file if it exists."
Write-Output "(If one is found in a folder located at  ../env/<name-of-ospos-root-folder>)"
Write-Output "============================================================================="

$currentfolder = Split-Path -Path (Get-Location) -Leaf
if(Test-Path -Path ../env/$currentfolder/.env -PathType Leaf)
{
Copy ../env/$currentfolder/.env
}

