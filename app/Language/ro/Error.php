<?php

return [
    'encryption_key_not_provisioned'   => 'Nu există o cheie de criptare provisionată. Rulați `php spark env:provision` înainte de a continua.',
    'no_permission_module'             => 'Nu aveți permisiunea de a accesa modulul numit',
    'throttle_key_not_provisioned'     => 'Nu există o cheie de limitare provisionată. Rulați `php spark env:provision` înainte de a continua.',
    'unable_to_create_env_file'        => 'Nu se poate crea {filePath} pentru a provisiona o cheie de mediu',
    'unable_to_lock_file'              => 'Nu se poate bloca {filePath}: {reason}',
    'unable_to_open_lock_file'         => 'Nu se poate deschide {filePath}: {reason}',
    'unable_to_persist_encryption_key' => 'Nu se poate salva cheia de criptare în {filePath}',
    'unable_to_persist_throttle_key'   => 'Nu se poate salva throttle.key în {filePath}',
    'unable_to_read_env_file'          => 'Nu se poate citi {filePath} pentru a provisiona o cheie de mediu',
    'unknown'                          => 'Eroare neașteptată',
];
