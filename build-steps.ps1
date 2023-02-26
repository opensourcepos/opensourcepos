# ------------------------------------------------------
# Run this Powershell script to "build" OSPOS.
# Execute this script from a terminal starting
# with the project root as the working directory.
# Use ".\build.ps1"
# The leading ".\" tells Powershell that you trust it.
# ------------------------------------------------------

Write-Output "============================================================================="
Write-Output "1. Run Composer Instsall, NPM Install, and install Grunt-CLI "
Write-Output "============================================================================="

composer install
npm install
npm install -g grunt-cli

Write-Output "============================================================================="
Write-Output "2. Run Bower Install "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

bower install

Write-Output "============================================================================="
Write-Output "3. Run NPM Install  for 0.4.5 "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"
cd grunt045

npm install

Write-Output "============================================================================="
Write-Output "4. Run task1 wiredep : 1.6.1 "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"
cd ../

grunt task1

Write-Output "============================================================================="
Write-Output "5. Run task2 bower_concat : 0.4.5 "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"
cd grunt045

grunt task2

Write-Output "============================================================================="
Write-Output "6. Run 'task3', ['bowercopy'] 1.6.1 "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"
cd ../

grunt task3

Write-Output "============================================================================="
Write-Output "7. Run 'task4', ['copy'] : 0.4.5 "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"
cd grunt045

grunt task4

Write-Output "============================================================================="
Write-Output "8. Run 'task5', ['concat', ...] :  1.6.1 "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"
cd ../

grunt concat

Write-Output "============================================================================="
Write-Output "9. Run 'task5', [...cat','uglify',...] :  1.6.1 "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

grunt uglify

Write-Output "============================================================================="
Write-Output "10 .Run 'task5', [...ify','cssmin',...] :  1.6.1 "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

grunt cssmin

Write-Output "============================================================================="
Write-Output "11. Run 'task5', [...min','injector',...] :  1.6.1 "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

grunt injector

Write-Output "============================================================================="
Write-Output "12. Run 'task5', [...tor','jshint'] :  1.6.1 "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

grunt jshint --force

Write-Output "============================================================================="
Write-Output "13. Run 'task6', ['cachebreaker',... : 0.4.5 "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"
cd grunt045

grunt cachebreaker

Write-Output "============================================================================="
Write-Output "14. Run 'task6', [...ker', 'clean:license',... : 0.4.5 "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

grunt clean:license

Write-Output "============================================================================="
Write-Output "15. Run 'task6', [...nse', 'license'... : 0.4.5 "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

grunt license

Write-Output "============================================================================="
Write-Output "16. Run 'task6', [...nse', 'bower-licensechecker'] : 0.4.5 "
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

grunt bower-licensechecker

Read-Host -Prompt "Press any key to continue"
Write-Output "============================================================================="
Write-Output "17. Run'task7', ['compress'] : 1.6.1 "
Write-Output "============================================================================="
cd ../

grunt compress

