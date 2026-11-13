<?php

require __DIR__ . '/../vendor/codeigniter4/framework/system/Test/bootstrap.php';

// Local/CI checkouts may never have hit a Config controller action that
// lazily provisions the encryption key, so the test suite needs to do it
// itself before anything touches Services::encrypter().
helper('security');
checkEncryption();
