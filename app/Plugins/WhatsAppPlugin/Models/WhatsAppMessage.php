<?php

namespace App\Plugins\WhatsAppPlugin\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use ReflectionException;

/**
 * Persists the WhatsApp conversation log: outbound messages the plugin sends and
 * inbound replies received via the webhook.
 */
class WhatsAppMessage extends Model
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
     * Records one message — outbound or inbound — in the conversation log.
     *
     * @return int The inserted message_id, or 0 on failure.
     *
     * @throws ReflectionException
     */
    public function storeWhatsAppMessage(array $data): int
    {
        if (empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        return $this->insert($data, true) ? (int) $this->getInsertID() : 0;
    }

    /**
     * Meta redelivers a webhook whose acknowledgement it did not see, so the same
     * inbound message can arrive twice. The unique index on wa_message_id is the
     * backstop; this keeps a redelivery from becoming a failed insert part-way
     * through a payload.
     */
    public function existsByWaMessageId(string $waMessageId): bool
    {
        return $this->db->table('whatsapp_messages')
            ->where('wa_message_id', $waMessageId)
            ->countAllResults() > 0;
    }

    /**
     * @param string $phone Normalized phone number (digits only).
     */
    public function getConversation(string $phone, int $limit = 200): ResultInterface
    {
        $builder = $this->db->table('whatsapp_messages');
        $builder->where('phone', $phone);
        $builder->orderBy('created_at', 'asc');
        $builder->orderBy('message_id', 'asc');
        $builder->limit($limit);

        return $builder->get();
    }

    /**
     * Distinct phone numbers with a conversation, most recently active first.
     */
    public function getRecentConversations(int $limit = 50): array
    {
        // Grouped by phone only: inbound webhook messages and general outbound
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
     * Updates the delivery status of an outbound message, identified by its
     * WhatsApp message id (wamid).
     *
     * Status callbacks can arrive out of order, so backward transitions along the
     * sent -> delivered -> read progression are ignored to stop a late "delivered"
     * downgrading an already "read" message. Statuses outside that ordering
     * (e.g. "failed") are always applied.
     *
     * The ordering lives in the WHERE clause rather than in a read-then-compare,
     * because two callbacks for the same message can be handled concurrently and
     * the later write would otherwise win on a stale comparison.
     *
     * @return bool False when the statement itself fails. A skipped backward
     *              transition and an unknown wamid both count as success.
     */
    public function updateStatus(string $waMessageId, string $status): bool
    {
        $rank = ['sent' => 1, 'delivered' => 2, 'read' => 3];

        $builder = $this->db->table('whatsapp_messages')->where('wa_message_id', $waMessageId);

        if (isset($rank[$status])) {
            $overtakes = array_keys(array_filter($rank, static fn (int $r): bool => $r < $rank[$status]));

            $builder->groupStart()
                ->where('status', null)
                ->orWhereNotIn('status', array_keys($rank));

            if ($overtakes !== []) {
                $builder->orWhereIn('status', $overtakes);
            }

            $builder->groupEnd();
        }

        return $builder->update(['status' => $status]);
    }
}
