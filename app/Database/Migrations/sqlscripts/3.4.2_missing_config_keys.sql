INSERT IGNORE INTO ospos_app_config (`key`, `value`)
SELECT 'msg_msg', ''
    WHERE NOT EXISTS (SELECT 1 FROM ospos_app_config WHERE `key` = 'msg_msg');

INSERT IGNORE INTO ospos_app_config (`key`, `value`)
SELECT 'msg_pwd', ''
    WHERE NOT EXISTS (SELECT 1 FROM ospos_app_config WHERE `key` = 'msg_pwd');

INSERT IGNORE INTO ospos_app_config (`key`, `value`)
SELECT 'msg_uid', ''
    WHERE NOT EXISTS (SELECT 1 FROM ospos_app_config WHERE `key` = 'msg_uid');

INSERT IGNORE INTO ospos_app_config (`key`, `value`)
SELECT 'msg_src', ''
    WHERE NOT EXISTS (SELECT 1 FROM ospos_app_config WHERE `key` = 'msg_src');

INSERT IGNORE INTO ospos_app_config (`key`, `value`)
SELECT 'smtp_timeout', 5000
    WHERE NOT EXISTS (SELECT 1 FROM ospos_app_config WHERE `key` = 'smtp_timeout');

INSERT IGNORE INTO ospos_app_config (`key`, `value`)
SELECT 'smtp_crypto', 'tls'
    WHERE NOT EXISTS (SELECT 1 FROM ospos_app_config WHERE `key` = 'smtp_crypto');

INSERT IGNORE INTO ospos_app_config (`key`, `value`)
SELECT 'smtp_port', 587
    WHERE NOT EXISTS (SELECT 1 FROM ospos_app_config WHERE `key` = 'smtp_port');

INSERT IGNORE INTO ospos_app_config (`key`, `value`)
SELECT 'mailpath', '/usr/bin/sendmail'
    WHERE NOT EXISTS (SELECT 1 FROM ospos_app_config WHERE `key` = 'mailpath');

INSERT IGNORE INTO ospos_app_config (`key`, `value`)
SELECT 'protocol', 'sendmail'
    WHERE NOT EXISTS (SELECT 1 FROM ospos_app_config WHERE `key` = 'protocol');
