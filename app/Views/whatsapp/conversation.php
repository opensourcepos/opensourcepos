<?php
/**
 * Renders a WhatsApp conversation thread.
 *
 * @var array $messages Array of message objects (oldest first).
 */
?>
<div class="whatsapp-conversation" style="max-height: 360px; overflow-y: auto; border: 1px solid #e5e5e5; border-radius: 4px; padding: 8px; background: #efeae2;">
    <?php if (empty($messages)): ?>
        <p class="text-muted" style="text-align: center; margin: 12px 0;"><?= lang('Whatsapp.no_messages') ?></p>
    <?php else: ?>
        <?php foreach ($messages as $message): ?>
            <?php $outbound = $message->direction === 'out'; ?>
            <div style="text-align: <?= $outbound ? 'right' : 'left' ?>; margin: 4px 0;">
                <div style="display: inline-block; max-width: 75%; padding: 6px 10px; border-radius: 8px; text-align: left; background: <?= $outbound ? '#dcf8c6' : '#ffffff' ?>; box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);">
                    <?php if ($message->type === 'document'): ?>
                        <div><span class="glyphicon glyphicon-file">&nbsp;</span><?= esc($message->filename) ?></div>
                        <?php if (!empty($message->body)): ?>
                            <div><?= nl2br(esc($message->body)) ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <?= nl2br(esc($message->body)) ?>
                    <?php endif; ?>
                    <div style="font-size: 10px; color: #888; margin-top: 2px;">
                        <?= esc($message->created_at) ?>
                        <?php if ($outbound && !empty($message->status)): ?>
                            &middot; <?= esc(lang('Whatsapp.status_' . $message->status)) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
