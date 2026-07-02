<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Registers the WhatsApp module (menu entry, permission and admin grant) and
 * seeds the WhatsApp Business Cloud API configuration keys. Mirrors the way the
 * existing "messages" (SMS) module is registered in initial_schema.sql.
 */
class AddWhatsappModule extends Migration
{
    public function up(): void
    {
        // Register the module so it appears in the office menu.
        $this->db->table('modules')->ignore(true)->insert([
            'name_lang_key' => 'module_whatsapp',
            'desc_lang_key' => 'module_whatsapp_desc',
            'sort'          => 101,
            'module_id'     => 'whatsapp',
        ]);

        // Permission gating the Whatsapp controller (Secure_Controller).
        $this->db->table('permissions')->ignore(true)->insert([
            'permission_id' => 'whatsapp',
            'module_id'     => 'whatsapp',
        ]);

        // Grant the permission to the default admin (person_id 1).
        $this->db->table('grants')->ignore(true)->insert([
            'permission_id' => 'whatsapp',
            'person_id'     => 1,
        ]);

        // Seed the WhatsApp Business Cloud API configuration keys.
        $this->db->table('app_config')->ignore(true)->insertBatch([
            ['key' => 'whatsapp_enabled', 'value' => '0'],
            ['key' => 'whatsapp_api_url', 'value' => 'https://graph.facebook.com'],
            ['key' => 'whatsapp_api_version', 'value' => 'v21.0'],
            ['key' => 'whatsapp_phone_id', 'value' => ''],
            ['key' => 'whatsapp_business_id', 'value' => ''],
            ['key' => 'whatsapp_token', 'value' => ''],
            ['key' => 'whatsapp_default_country_code', 'value' => ''],
            ['key' => 'whatsapp_msg', 'value' => ''],
            ['key' => 'whatsapp_verify_token', 'value' => ''],
            ['key' => 'whatsapp_app_secret', 'value' => ''],
        ]);

        // Conversation log: every outbound message we send and every inbound
        // reply received via the webhook is stored here so the WhatsApp page can
        // render the full interaction with a customer.
        $this->forge->addField([
            'message_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'person_id'     => ['type' => 'INT', 'constraint' => 10, 'null' => true],
            'phone'         => ['type' => 'VARCHAR', 'constraint' => 20],
            'direction'     => ['type' => 'VARCHAR', 'constraint' => 3],  // 'in' | 'out'
            'type'          => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'text'],
            'body'          => ['type' => 'TEXT', 'null' => true],
            'media_id'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'filename'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'wa_message_id' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => true],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'error'         => ['type' => 'TEXT', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('message_id', true);
        $this->forge->addKey('phone');
        $this->forge->addKey('person_id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('whatsapp_messages', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('whatsapp_messages', true);

        $this->db->table('grants')
            ->where(['permission_id' => 'whatsapp', 'person_id' => 1])
            ->delete();

        $this->db->table('permissions')
            ->where('permission_id', 'whatsapp')
            ->delete();

        $this->db->table('modules')
            ->where('module_id', 'whatsapp')
            ->delete();

        $this->db->table('app_config')
            ->whereIn('key', [
                'whatsapp_enabled',
                'whatsapp_api_url',
                'whatsapp_api_version',
                'whatsapp_phone_id',
                'whatsapp_business_id',
                'whatsapp_token',
                'whatsapp_default_country_code',
                'whatsapp_msg',
                'whatsapp_verify_token',
                'whatsapp_app_secret',
            ])
            ->delete();
    }
}
