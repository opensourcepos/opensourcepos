<?= view('partial/header') ?>

<div id="table_action_header">
    <ul>
        <li class="float_right">
            <button class="btn btn-primary btn-sm" id="new_account_btn" title="New Account">
                <span class="glyphicon glyphicon-plus">&nbsp;</span>New Account
            </button>
        </li>
    </ul>
</div>

<div id="table_holder">
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>Account ID</th>
                <th>Code</th>
                <th>Name</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($accounts as $account): ?>
            <tr>
                <td><?= esc($account->account_id) ?></td>
                <td><?= esc($account->code) ?></td>
                <td><?= esc($account->name) ?></td>
                <td><?= esc($account->type) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal logic here -->
<?= view('partial/footer') ?>
