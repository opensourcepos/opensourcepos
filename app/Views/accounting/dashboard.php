<?= view('partial/header') ?>

<div id="table_action_header">
    <div class="row">
        <div class="col-md-12 text-center">
            <div class="btn-group" role="group">
                <a href="<?= base_url('accounting/accounts') ?>" class="btn btn-primary">
                    <span class="glyphicon glyphicon-list-alt"></span> Chart of Accounts
                </a>
                <a href="<?= base_url('accounting/entries') ?>" class="btn btn-primary">
                    <span class="glyphicon glyphicon-book"></span> Journal Entries
                </a>
            </div>
        </div>
    </div>
</div>

<br/>

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Balance Sheet Summary</h3>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Code</th>
                            <th>Account Name</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($balance_sheet)): ?>
                            <tr><td colspan="4" class="text-center">No data available. Complete transactions to generate entries.</td></tr>
                        <?php else: ?>
                            <?php foreach($balance_sheet as $row): ?>
                            <tr>
                                <td><?= esc($row->type) ?></td>
                                <td><?= esc($row->code) ?></td>
                                <td><?= esc($row->name) ?></td>
                                <td><?= to_currency($row->balance) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Profit & Loss Summary</h3>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Code</th>
                            <th>Account Name</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($profit_loss)): ?>
                            <tr><td colspan="4" class="text-center">No data available. Complete transactions to generate entries.</td></tr>
                        <?php else: ?>
                            <?php foreach($profit_loss as $row): ?>
                            <tr>
                                <td><?= esc($row->type) ?></td>
                                <td><?= esc($row->code) ?></td>
                                <td><?= esc($row->name) ?></td>
                                <td><?= to_currency($row->balance) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= view('partial/footer') ?>
