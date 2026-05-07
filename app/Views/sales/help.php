<?php
/**
 * @var array $keyboardShortcuts
 */

$keyboardShortcuts ??= [];

$shortcut_labels = [
    'cancel'    => lang('Sales.key_cancel'),
    'items'     => lang('Sales.key_item_search'),
    'customers' => lang('Sales.key_customer_search'),
    'suspend'   => lang('Sales.key_suspend'),
    'suspended' => lang('Sales.key_suspended'),
    'amount'    => lang('Sales.key_tendered'),
    'payment'   => lang('Sales.key_payment'),
    'complete'  => lang('Sales.key_finish_sale'),
    'finish'    => lang('Sales.key_finish_quote'),
    'help'      => lang('Sales.key_help_modal')
];
?>

<div class="container-fluid">

    <ul class="nav nav-tabs" id="SCTabs" data-toggle="tab">
        <li class="active"><a href="#system_shortcuts" data-toggle="tab" title="<?= lang('Sales.key_system'); ?>"><?= lang('Sales.key_system'); ?></a></li>
        <li><a href="#browser_shortcuts" data-toggle="tab" title="<?= lang('Sales.key_browser'); ?>"><?= lang('Sales.key_browser'); ?></a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="system_shortcuts">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th><?= lang('Sales.key_help'); ?></th>
                        <th><?= lang('Sales.key_function'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shortcut_labels as $name => $label): ?>
                        <?php $shortcut = $keyboardShortcuts[$name] ?? ['label' => '', 'code' => '']; ?>
                        <tr>
                            <td><code><?= esc($shortcut['label'] !== '' ? $shortcut['label'] : $shortcut['code']) ?></code></td>
                            <td><?= esc($label) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="tab-pane" id="browser_shortcuts">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th><?= lang('Sales.key_help'); ?></th>
                        <th><?= lang('Sales.key_function'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>F11</code></td>
                        <td><?= lang('Sales.key_full'); ?></td>
                    </tr>
                    <tr>
                        <td><code>CTRL + </code></td>
                        <td><?= lang('Sales.key_in'); ?></td>
                    </tr>
                    <tr>
                        <td><code>CTRL -</code></td>
                        <td><?= lang('Sales.key_out'); ?></td>
                    </tr>
                    <tr>
                        <td><code>CTRL + 0</code></td>
                        <td><?= lang('Sales.key_restore'); ?></td>
                    </tr>
                    <tr>
                        <td><code>CTRL + P</code></td>
                        <td><?= lang('Sales.key_print'); ?></td>
                    </tr>
                    <tr>
                        <td><code>CTRL + F</code></td>
                        <td><?= lang('Sales.key_search'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
