<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use ReflectionException;

/**
 * Whatsapp_message class
 *
 * Persists the WhatsApp conversation log (outbound messages we send and inbound
 * replies received via the webhook) so the full interaction with a customer can
 * be displayed.
 */
class Whatsapp_message extends Model
{
    protected $table            = 'whatsapp_messages';
    protected $primaryKey       = 'message_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $returnType       = 'object';
    protected $allowedFields    = [
        'person_id',
        'phone',
        'direction',
        'type',
        'body',
        'media_id',
        'filename',
        'wa_message_id',
        'status',
        'error',
        'created_at',
    ];

    /**
     * Records a message in the conversation log.
     *
     * @param array $data Row data (direction, phone, body, ...).
     *
     * @return int The inserted message_id, or 0 on failure.
     *
     * @throws ReflectionException
     */
    public function log(array $data): int
    {
        if (empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        return $this->insert($data, true) ? (int) $this->getInsertID() : 0;
    }

    /**
     * Returns the conversation for a given phone number ordered oldest first.
     *
     * @param string $phone Normalized phone number (digits only).
     * @param int    $limit Maximum number of messages to return.
     */
    public function get_conversation(string $phone, int $limit = 200): ResultInterface
    {
        $builder = $this->db->table('whatsapp_messages');
        $builder->where('phone', $phone);
        $builder->orderBy('created_at', 'asc');
        $builder->orderBy('message_id', 'asc');
        $builder->limit($limit);

        return $builder->get();
    }

    /**
     * Returns the distinct phone numbers that have a conversation, most recently
     * active first, along with the latest message preview.
     *
     * @param int $limit Maximum number of conversations to return.
     */
    public function get_recent_conversations(int $limit = 50): array
    {
        // Group by phone only: inbound webhook messages and general outbound
        // sends log person_id => null, while sale sends log a real person_id.
        // Grouping on both would split one customer's thread into two rows.
        $builder = $this->db->table('whatsapp_messages');
        $builder->select('phone, MAX(person_id) AS person_id, MAX(created_at) AS last_activity, COUNT(*) AS message_count');
        $builder->groupBy('phone');
        $builder->orderBy('last_activity', 'desc');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }

    /**
     * Updates the delivery status of an outbound message identified by its
     * WhatsApp message id (wamid), used by webhook status callbacks.
     *
     * @param string $wa_message_id The WhatsApp message id.
     * @param string $status        The new status (sent|delivered|read|failed).
     */
    public function update_status(string $wa_message_id, string $status): bool
    {
        $builder = $this->db->table('whatsapp_messages');
        $builder->where('wa_message_id', $wa_message_id);

        return $builder->update(['status' => $status]);
    }
}
