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
                    <tr>
                        <td><code>ESC</code></td>
                        <td><?= lang('Sales.key_cancel'); ?></td>
                    </tr>
                    <tr>
                        <td><code>ALT + 1</code></td>
                        <td><?= lang('Sales.key_item_search'); ?></td>
                    </tr>
                    <tr>
                        <td><code>ALT + 2</code></td>
                        <td><?= lang('Sales.key_customer_search'); ?></td>
                    </tr>
                    <tr>
                        <td><code>ALT + 3</code></td>
                        <td><?= lang('Sales.key_suspend'); ?></td>
                    </tr>
                    <tr>
                        <td><code>ALT + 4</code></td>
                        <td><?= lang('Sales.key_suspended'); ?></td>
                    </tr>
                    <tr>
                        <td><code>ALT + 5</code></td>
                        <td><?= lang('Sales.key_tendered'); ?></td>
                    </tr>
                    <tr>
                        <td><code>ALT + 6</code></td>
                        <td><?= lang('Sales.key_payment'); ?></td>
                    </tr>
                    <tr>
                        <td><code>ALT + 7</code></td>
                        <td><?= lang('Sales.key_finish_sale'); ?></td>
                    </tr>
                    <tr>
                        <td><code>ALT + 8</code></td>
                        <td><?= lang('Sales.key_finish_quote'); ?></td>
                    </tr>
                    <tr>
                        <td><code>ALT + 9</code></td>
                        <td><?= lang('Sales.key_help_modal'); ?></td>
                    </tr>
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
