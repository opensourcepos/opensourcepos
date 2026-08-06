<?php

namespace App\Plugins\WhatsAppPlugin\Migrations;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;

/**
 * Conversation log table.
 *
 * Dropped by WhatsAppPlugin::uninstall() — `down()` is never called by the
 * PluginManager, so it is deliberately not defined here.
 */
class CreateWhatsAppMessagesTable
{
    public function __construct(
        private BaseConnection $db,
        private Forge $forge,
    ) {
    }

    public function up(): void
    {
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
        // Looked up on every inbound status webhook callback (update_status()).
        $this->forge->addKey('wa_message_id');

        // utf8mb4 so emoji and full multilingual message content can be stored.
        $this->forge->createTable('whatsapp_messages', true, [
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }
}
