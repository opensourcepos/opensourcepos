<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Jobs extends BaseConfig
{
    public string $mode = 'web';   // auto | web | manual
    public int $webMaxSeconds = 5;
}
