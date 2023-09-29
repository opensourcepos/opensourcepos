# ------------------------------------------------------
# Run this Powershell script to "build" OSPOS.
# Execute this script from a terminal starting
# with the project root as the working directory.
# Use ".\build.ps1"
# The leading ".\" tells Powershell that you trust it.
# ------------------------------------------------------
# Tested with:
# Composer version 2.5.1
# Node.js version 18.14.0
# npm version 9.4.2
# ------------------------------------------------------

Write-Output "============================================================================="
Write-Output "Before continuing, delete the package-lock.json and  clear the dependencies"
Write-Output "from package.json"
Write-Output "============================================================================="
Read-Host -Prompt "Press any key to continue"

# npm i npm@9.4.2

Write-Output "bootstrap"
npm i bootstrap@3.4.1

Write-Output "bootswatch"
npm i bootswatch@3.4.1

Write-Output "bootstrap-daterangepicker"
npm i bootstrap-daterangepicker@2.1.27

Write-Output "bootstrap-datetimepicker"
npm i git://github.com/smalot/bootstrap-datetimepicker.git#master

Write-Output "bootstrap-notify"
npm i bootstrap-notify@3.1.3

Write-Output "bootstrap-select"
npm i bootstrap-select@1.13.18

Write-Output "bootstrap-table"
npm i bootstrap-table@1.18.1

Write-Output "bootstrap-tagsinput"
npm i bootstrap-tagsinput@0.7.1

Write-Output "bootstrap-toggle"
npm i bootstrap-toggle@2.2.2

Write-Output "bootstrap3-dialog"
npm i git://github.com/nakupanda/bootstrap3-dialog.git#master

Write-Output "jasny-bootstrap"
npm i jasny-bootstrap@3.1.3

Write-Output "bootstrap5"
npm i bootstrap5@npm:bootstrap@5.2.3

Write-Output "bootswatch5"
npm i bootswatch5@npm:bootswatch@5.2.3

Write-Output "jquery"
npm i jquery@2.1.4

Write-Output "jquery-ui-dist"
npm i jquery-ui-dist@1.12.1

Write-Output "jquery-form"
npm i jquery-form@4.3.0

Write-Output "tableexport.jquery.plugin"
npm i tableexport.jquery.plugin@1.27.0

Write-Output "jquery-validation"
npm i jquery-validation@1.19.5

Write-Output "clipboard"
npm i clipboard@2.0.11

Write-Output "chartist"
npm i chartist@0.11.4

Write-Output "chartist-plugin-axistitle"
npm i chartist-plugin-axistitle@0.0.7

Write-Output "chartist-plugin-pointlabels"
npm i chartist-plugin-pointlabels@0.0.4

Write-Output "chartist-plugin-barlabels"
npm i chartist-plugin-barlabels@0.0.5

Write-Output "chartist-plugin-tooltip"
npm i git://github.com/tmmdata/chartist-plugin-tooltip.git#v0.0.18

Write-Output "coffeescript"
npm i coffeescript@2.7.0

Write-Output "html2canvas"
npm i html2canvas@1.4.1

Write-Output "js-cookie"
npm i js-cookie@2.2.1

Write-Output "jspdf"
npm i jspdf@1.3.4

Write-Output "jspdf-autotable"
npm i jspdf-autotable@2.0.17

Write-Output "es6-promise"
npm i es6-promise@4.2.8

Write-Output "npm-check-updates"
npm i -D npm-check-updates@16.6.5

Write-Output "gulp"
npm i -D gulp@4.0.2

Write-Output "gulp-clean"
npm i -D gulp-clean@0.4.0

Write-Output "gulp-rev"
npm i -D gulp-rev@10.0.0

Write-Output "gulp-inject"
npm i -D gulp-inject@5.0.5

Write-Output "gulp-concat"
npm i -D gulp-concat@2.6.1

Write-Output "gulp-clean-css"
npm i -D gulp-clean-css

Write-Output "gulp-rename"
npm i -D gulp-rename

Write-Output "gulp-uglify"
npm i -D gulp-uglify

Write-Output "gulp-debug"
npm i -D gulp-debug

Write-Output "stream-series"
npm i -D stream-series

Write-Output "readable-stream"
npm i -D readable-stream
