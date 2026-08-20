INSERT IGNORE INTO ospos_app_config (`key`, `value`)
VALUES
    ('msg_msg', ''),
    ('msg_pwd', ''),
    ('msg_uid', ''),
    ('msg_src', ''),
    ('smtp_timeout', 5000),
    ('smtp_crypto', 'tls'),
    ('smtp_port', 587),
    ('mailpath', '/usr/bin/sendmail'),
    ('protocol', 'sendmail');
