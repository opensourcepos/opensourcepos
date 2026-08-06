<?php
/**
 * "Send via WhatsApp" button injected into a sale document view. The plugin has
 * already confirmed the customer has a phone number.
 *
 * @var int    $saleId
 * @var string $documentType One of invoice|quote|work_order|receipt.
 */
?>
<a href="javascript:void(0);">
    <div class="btn btn-success btn-sm" id="show_whatsapp_button">
        <?= '<span class="glyphicon glyphicon-comment">&nbsp;</span>' . lang('WhatsAppPlugin.send_whatsapp') ?>
    </div>
</a>

<script type="text/javascript">
    $(document).ready(function() {
        $("#show_whatsapp_button").click(function() {
            var $btn = $(this);

            if ($btn.hasClass('disabled')) {
                return;
            }

            $btn.addClass('disabled');

            $.get('<?= site_url('whatsapp/sendDocument/' . $saleId . '/' . $documentType) ?>',
                function(response) {
                    $.notify({
                        message: response.message
                    }, {
                        type: response.success ? 'success' : 'danger'
                    })
                }, 'json'
            ).always(function() {
                $btn.removeClass('disabled');
            });
        });
    });
</script>
