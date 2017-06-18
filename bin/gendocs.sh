#!/bin/sh

composer require --dev apigen/apigen
grunt gendocs
composer remove --dev apigen/apigen
composer clearcache


