#!/bin/sh

## Part of CodeIgniter Composer Installer
##
## @author     Kenji Suzuki <https://github.com/kenjis>
## @license    MIT License
## @copyright  2015 Kenji Suzuki
## @link       https://github.com/kenjis/codeigniter-composer-installer

cd `dirname $0`
cd ..

diff -urN vendor/codeigniter/framework/application application
diff -u vendor/codeigniter/framework/index.php public/index.php
