<?php

namespace App\Plugins\WhatsAppPlugin\Libraries;

/**
 * One outbound WhatsApp message as it is recorded in the conversation log.
 *
 * The send methods build this up as the send progresses — a media id once the
 * document is uploaded, a message id and status once Meta has answered — so the
 * log write takes a single argument instead of a long positional parameter list.
 *
 * Status defaults to 'failed': every early return is a failure, and only a
 * successful send has to say otherwise.
 */
class OutboundMessage
{
    public ?string $mediaId     = null;
    public ?string $filename    = null;
    public ?string $waMessageId = null;
    public string $status       = 'failed';
    public ?string $error       = null;

    public function __construct(
        public string $phone,
        public string $type,
        public ?string $body = null,
        public ?int $personId = null,
    ) {
    }

    /**
     * @return array The row shape WhatsAppMessage::storeWhatsAppMessage() expects.
     */
    public function toArray(): array
    {
        return [
            'person_id'     => $this->personId,
            'phone'         => $this->phone,
            'direction'     => 'out',
            'type'          => $this->type,
            'body'          => $this->body,
            'media_id'      => $this->mediaId,
            'filename'      => $this->filename,
            'wa_message_id' => $this->waMessageId,
            'status'        => $this->status,
            'error'         => $this->error,
        ];
    }
}
