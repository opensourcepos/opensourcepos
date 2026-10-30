<?php

return [
    'encryption_key_not_provisioned'   => 'ยังไม่พบคีย์เข้ารหัส โปรดเรียกใช้คำสั่ง `php spark env:provision` ก่อนดำเนินการต่อ',
    'no_permission_module'             => 'คุณไม่ได้รับสิทธิ์การเข้าถึงข้อมูลในส่วนนี้',
    'throttle_key_not_provisioned'     => 'ยังไม่ได้กำหนดคีย์ควบคุมการทำงาน (throttle key) กรุณาเรียกใช้คำสั่ง `php spark env:provision` ก่อนดำเนินการต่อ',
    'unable_to_create_env_file'        => 'ไม่สามารถสร้าง {filePath} เพื่อกำหนดค่า throttle.key ได้',
    'unable_to_lock_file'              => 'ไม่สามารถล็อกไฟล์ {filePath} ได้: {reason}',
    'unable_to_open_lock_file'         => 'ไม่สามารถเปิดไฟล์ {filePath} ได้: {reason}',
    'unable_to_persist_encryption_key' => 'ไม่สามารถบันทึกการเข้ารหัสไปยังไฟล์ {filePath} ได้',
    'unable_to_persist_throttle_key'   => 'ไม่สามารถบันทึก throttle.key ลงใน {filePath} ได้',
    'unable_to_read_env_file'          => 'ไม่สามารถอ่าน {filePath} เพื่อกำหนดค่า throttle.key ได้',
    'unknown'                          => 'ไม่ทราบ',
];
