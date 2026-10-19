<?php

use App\Models\Attribute;
use App\Models\Employee;
use CodeIgniter\Database\ResultInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Session\Session;
use Config\OSPOS;
use Config\Services;

/**
 * Tabular views helper
 */

/**
 * Basic tabular headers function
 */
function transform_headers_readonly(array $headers): string
{
    $result = [];

    foreach ($headers as $key => $value) {
        $result[] = ['field' => $key, 'title' => $value, 'sortable' => $value != '', 'switchable' => !preg_match('(^$|&nbsp)', $value)];
    }

    return json_encode($result);
}

/**
 * Basic tabular headers function
 */
function transform_headers(array $headers, bool $readonly = false, bool $editable = true): string    // TODO: $array needs to be refactored to a new name.  Perhaps $headers?
{
    $result = [];

    if (!$readonly) {
        $headers = array_merge([['checkbox' => 'select', 'sortable' => false]], $headers);
    }

    if ($editable) {
        $headers[] = ['edit' => ''];
    }

    foreach ($headers as $element) {    // TODO: This might be clearer to refactor this to `foreach($headers as $header)`
        reset($element);
        $result[] = [
            'field'      => key($element),
            'title'      => current($element),
            'switchable' => $element['switchable'] ?? !preg_match('(^$|&nbsp)', current($element)),
            'escape'     => !preg_match("/(edit|email|messages|item_pic)/", key($element)) && !(isset($element['escape']) && !$element['escape']),
            'sortable'   => $element['sortable'] ?? current($element) != '',
            'checkbox'   => $element['checkbox'] ?? false,
            'class'      => isset($element['checkbox']) || preg_match('(^$|&nbsp)', current($element)) ? 'print_hide' : '',
            'sorter'     => $element['sorter'] ?? ''
        ];
    }

    return json_encode($result);
}


function salesHeaders(): array
{
    return [
        ['sale_id'         => lang('Common.id')],
        ['sale_time'       => lang('Sales.sale_time')],
        ['customer_name'   => lang('Customers.customer')],
        ['amount_due'      => lang('Sales.amount_due')],
        ['amount_tendered' => lang('Sales.amount_tendered')],
        ['change_due'      => lang('Sales.change_due')],
        ['payment_type'    => lang('Sales.payment_type')]
    ];
}

/**
 * Get the header for the sales tabular view
 */
function get_sales_manage_table_headers(): string
{
    $headers = salesHeaders();
    $config = config(OSPOS::class)->settings;

    if ($config['invoice_enable']) {
        $headers[] = ['invoice_number' => lang('Sales.invoice_number')];
        $headers[] = ['invoice' => '', 'sortable' => false, 'escape' => false];
    }

    $headers[] = ['receipt' => '', 'sortable' => false, 'escape' => false];

    return transform_headers($headers);
}

/**
 * Get the html data row for the sales
 */
function getSaleDataRow(object $sale): array
{
    $uri = current_url(true);
    $controller = $uri->getSegment(1);

    $row = [
        'sale_id'         => $sale->sale_id,
        'sale_time'       => to_datetime(strtotime($sale->sale_time)),
        'customer_name'   => $sale->customer_name,
        'amount_due'      => to_currency($sale->amount_due),
        'amount_tendered' => to_currency($sale->amount_tendered),
        'change_due'      => to_currency($sale->change_due),
        'payment_type'    => $sale->payment_type
    ];

    $config = config(OSPOS::class)->settings;

    if ($config['invoice_enable']) {
        $row['invoice_number'] = $sale->invoice_number;
        $row['invoice'] = empty($sale->invoice_number)
            ? '-'
            : anchor(
                "$controller/invoice/$sale->sale_id",
                '<span class="glyphicon glyphicon-list-alt"></span>',
                ['title' => lang('Sales.show_invoice')]
            );
    }

    $row['receipt'] = anchor(
        "$controller/receipt/$sale->sale_id",
        '<span class="glyphicon glyphicon-usd"></span>',
        ['title' => lang('Sales.show_receipt')]
    );
    $row['edit'] = anchor(
        "$controller/edit/$sale->sale_id",
        '<span class="glyphicon glyphicon-edit"></span>',
        [
            'class'           => 'modal-dlg print_hide',
            'data-btn-delete' => lang('Common.delete'),
            'data-btn-submit' => lang('Common.submit'),
            'title'           => lang(ucfirst($controller) . ".update")
        ]
    );

    return $row;
}

/**
 * Column set for exporting sales: same as salesHeaders(), plus invoice_number when
 * invoice_enable is on, minus the invoice/receipt/edit icon-only anchor columns.
 */
function salesExportHeaders(): array
{
    $headers = salesHeaders();
    $config = config(OSPOS::class)->settings;

    if ($config['invoice_enable']) {
        $headers[] = ['invoice_number' => lang('Sales.invoice_number')];
    }

    return $headers;
}

/**
 * Export row for a sale: raw (unformatted) values, matching salesExportHeaders()'s column set.
 */
function getSaleExportRow(object $sale): array
{
    $row = [
        'sale_id'         => $sale->sale_id,
        'sale_time'       => $sale->sale_time,
        'customer_name'   => $sale->customer_name,
        'amount_due'      => $sale->amount_due,
        'amount_tendered' => $sale->amount_tendered,
        'change_due'      => $sale->change_due,
        'payment_type'    => $sale->payment_type
    ];

    $config = config(OSPOS::class)->settings;

    if ($config['invoice_enable']) {
        $row['invoice_number'] = $sale->invoice_number;
    }

    return $row;
}

/**
 * Get the html data last row for the sales
 */
function getSaleDataLastRow(ResultInterface $sales): array
{
    $sum_amount_due = 0;
    $sum_amount_tendered = 0;
    $sum_change_due = 0;

    foreach ($sales->getResult() as $key => $sale) {
        $sum_amount_due += $sale->amount_due;
        $sum_amount_tendered += $sale->amount_tendered;
        $sum_change_due += $sale->change_due;
    }

    return [
        'sale_id'         => '-',
        'sale_time'       => lang('Sales.total'),
        'amount_due'      => to_currency($sum_amount_due),
        'amount_tendered' => to_currency($sum_amount_tendered),
        'change_due'      => to_currency($sum_change_due)
    ];
}

/**
 * Get the sales payments summary
 */
function getSalesManagePaymentsSummary(array $payments): string
{
    $table = '<div id="report_summary">';
    $total = 0;

    foreach ($payments as $key => $payment) {
        $amount = $payment['payment_amount'];
        $total = bcadd($total, $amount);
        $table .= '<div class="summary_row">' . $payment['payment_type'] . ': ' . to_currency($amount) . '</div>';
    }

    $table .= '<div class="summary_row">' . lang('Sales.total') . ': ' . to_currency($total) . '</div>';
    $table .= '</div>';

    return $table;
}

function person_headers(): array
{
    return [
        ['people.person_id' => lang('Common.id')],
        ['last_name'        => lang('Common.last_name')],
        ['first_name'       => lang('Common.first_name')],
        ['email'            => lang('Common.email')],
        ['phone_number'     => lang('Common.phone_number')]
    ];
}


/**
 * Get the header for the people tabular view
 */
function get_people_manage_table_headers(): string
{
    $headers = person_headers();

    $employee = model(Employee::class);
    $session = session();

    if ($employee->has_grant('messages', $session->get('person_id'))) {
        $headers[] = ['messages' => '', 'sortable' => false];
    }

    return transform_headers($headers);
}

/**
 * Get the html data row for the person
 */
/**
 * Column set for exporting employees: person_headers() minus the conditional messages column.
 */
function employeeExportHeaders(): array
{
    return person_headers();
}

/**
 * Export row for an employee: raw (unformatted) values, matching employeeExportHeaders()'s column set.
 */
function getEmployeeExportRow(object $person): array
{
    return [
        'people.person_id' => $person->person_id,
        'last_name'        => $person->last_name,
        'first_name'       => $person->first_name,
        'email'            => $person->email,
        'phone_number'     => $person->phone_number
    ];
}

function get_person_data_row(object $person): array
{
    $controller = get_controller();

    return [
        'people.person_id' => $person->person_id,
        'last_name'        => $person->last_name,
        'first_name'       => $person->first_name,
        'email'            => empty($person->email) ? '' : mailto(esc($person->email), esc($person->email)),
        'phone_number'     => $person->phone_number,
        'messages'         => empty($person->phone_number)
            ? ''
            : anchor(
                "Messages/view/$person->person_id",
                '<span class="glyphicon glyphicon-phone"></span>',
                [
                    'class'           => 'modal-dlg',
                    'data-btn-submit' => lang('Common.submit'),
                    'title'           => lang('Messages.sms_send')
                ]
            ),
        'edit'             => anchor(
            "$controller/view/$person->person_id",
            '<span class="glyphicon glyphicon-edit"></span>',
            [
                'class'           => 'modal-dlg',
                'data-btn-submit' => lang('Common.submit'),
                'title'           => lang(ucfirst($controller) . '.update')    // TODO: String interpolation
            ]
        )
    ];
}


function customer_headers(): array
{
    return [
        ['people.person_id' => lang('Common.id')],
        ['last_name'        => lang('Common.last_name')],
        ['first_name'       => lang('Common.first_name')],
        ['email'            => lang('Common.email')],
        ['phone_number'     => lang('Common.phone_number')],
        ['total'            => lang('Common.total_spent'), 'sortable' => false]
    ];
}

/**
 * Get the header for the customer tabular view
 */
function get_customer_manage_table_headers(): string
{
    $headers = customer_headers();

    $employee = model(Employee::class);
    $session = session();

    if ($employee->has_grant('messages', $session->get('person_id'))) {
        $headers[] = ['messages' => '', 'sortable' => false];
    }

    return transform_headers($headers);
}

/**
 * Get the html data row for the customer
 */
/**
 * Column set for exporting customers: customer_headers() minus the conditional messages column.
 */
function customerExportHeaders(): array
{
    return customer_headers();
}

/**
 * Export row for a customer: raw (unformatted) values, matching customerExportHeaders()'s column set.
 */
function getCustomerExportRow(object $person, object $stats): array
{
    return [
        'people.person_id' => $person->person_id,
        'last_name'        => $person->last_name,
        'first_name'       => $person->first_name,
        'email'            => $person->email,
        'phone_number'     => $person->phone_number,
        'total'            => $stats->total
    ];
}

function get_customer_data_row(object $person, object $stats): array
{
    $controller = get_controller();

    return [
        'people.person_id' => $person->person_id,
        'last_name'        => $person->last_name,
        'first_name'       => $person->first_name,
        'email'            => empty($person->email) ? '' : mailto(esc($person->email), esc($person->email)),
        'phone_number'     => $person->phone_number,
        'total'            => to_currency($stats->total),
        'messages'         => empty($person->phone_number)
            ? ''
            : anchor(
                "Messages/view/$person->person_id",
                '<span class="glyphicon glyphicon-phone"></span>',
                [
                    'class'           => 'modal-dlg',
                    'data-btn-submit' => lang('Common.submit'),
                    'title'           => lang('Messages.sms_send')
                ]
            ),
        'edit'             => anchor(
            "$controller/view/$person->person_id",
            '<span class="glyphicon glyphicon-edit"></span>',
            [
                'class'           => 'modal-dlg',
                'data-btn-submit' => lang('Common.submit'),
                'title'           => lang(ucfirst($controller) . ".update")
            ]
        )
    ];
}

function supplier_headers(): array
{
    return [
        ['people.person_id' => lang('Common.id')],
        ['company_name'     => lang('Suppliers.company_name')],
        ['agency_name'      => lang('Suppliers.agency_name')],
        ['category'         => lang('Suppliers.category')],
        ['last_name'        => lang('Common.last_name')],
        ['first_name'       => lang('Common.first_name')],
        ['email'            => lang('Common.email')],
        ['phone_number'     => lang('Common.phone_number')]
    ];
}

/**
 * Get the header for the suppliers tabular view
 */
function get_suppliers_manage_table_headers(): string
{
    $headers = supplier_headers();

    $employee = model(Employee::class);
    $session = session();

    if ($employee->has_grant('messages', $session->get('person_id'))) {
        $headers[] = ['messages' => ''];
    }

    return transform_headers($headers);
}

/**
 * Get the html data row for the supplier
 */
/**
 * Column set for exporting suppliers: supplier_headers() minus the conditional messages column.
 */
function supplierExportHeaders(): array
{
    return supplier_headers();
}

/**
 * Export row for a supplier: raw (unformatted) values, matching supplierExportHeaders()'s column set.
 */
function getSupplierExportRow(object $supplier): array
{
    return [
        'people.person_id' => $supplier->person_id,
        'company_name'     => html_entity_decode($supplier->company_name),
        'agency_name'      => $supplier->agency_name,
        'category'         => $supplier->category,
        'last_name'        => $supplier->last_name,
        'first_name'       => $supplier->first_name,
        'email'            => $supplier->email,
        'phone_number'     => $supplier->phone_number
    ];
}

function get_supplier_data_row(object $supplier): array
{
    $controller = get_controller();

    return [
        'people.person_id' => $supplier->person_id,
        'company_name'     => html_entity_decode($supplier->company_name),
        'agency_name'      => $supplier->agency_name,
        'category'         => $supplier->category,
        'last_name'        => $supplier->last_name,
        'first_name'       => $supplier->first_name,
        'email'            => empty($supplier->email) ? '' : mailto(esc($supplier->email), esc($supplier->email)),
        'phone_number'     => $supplier->phone_number,
        'messages'         => empty($supplier->phone_number)
            ? ''
            : anchor(
                "Messages/view/$supplier->person_id",
                '<span class="glyphicon glyphicon-phone"></span>',
                [
                    'class'           => "modal-dlg",
                    'data-btn-submit' => lang('Common.submit'),
                    'title'           => lang('Messages.sms_send')
                ]
            ),
        'edit'             => anchor(
            "$controller/view/$supplier->person_id",
            '<span class="glyphicon glyphicon-edit"></span>',
            [
                'class'           => "modal-dlg",
                'data-btn-submit' => lang('Common.submit'),
                'title'           => lang(ucfirst($controller) . ".update")
            ]
        )
    ];
}

function item_headers(): array
{
    return [
        ['items.item_id' => lang('Common.id')],
        ['item_number'   => lang('Items.item_number')],
        ['name'          => lang('Items.name')],
        ['category'      => lang('Items.category')],
        ['company_name'  => lang('Suppliers.company_name')],
        ['cost_price'    => lang('Items.cost_price')],
        ['unit_price'    => lang('Items.unit_price')],
        ['quantity'      => lang('Items.quantity')]
    ];
}

/**
 * Get all sortable column keys for items table, including dynamic attribute columns.
 *
 * @param array|null $definitionIds Attribute definition IDs to append as sortable columns.
 *                                  If null, resolved via a query against SHOW_IN_ITEMS.
 * @return array Array of column headers in the format sanitizeSortColumn() expects
 */
function itemSortColumns(?array $definitionIds = null): array
{
    if ($definitionIds === null) {
        $attribute = model(Attribute::class);
        $definitionIds = array_keys($attribute->getDefinitionsByFlags($attribute::SHOW_IN_ITEMS));
    }

    $headers = item_headers();

    foreach ($definitionIds as $definitionId) {
        $headers[] = [(string) $definitionId => ''];
    }

    return $headers;
}

/**
 * Get the header for the items tabular view
 */
function getItemsManageTableHeaders(): string
{
    $attribute = model(Attribute::class);
    $config = config(OSPOS::class)->settings;
    $definitionsWithTypes = $attribute->getDefinitionsByFlags($attribute::SHOW_IN_ITEMS, true);

    $headers = item_headers();

    if ($config['use_destination_based_tax']) {
        $headers[] = ['tax_percents' => lang('Items.tax_category'), 'sortable' => false];
    } else {
        $headers[] = ['tax_percents' => lang('Items.tax_percents'), 'sortable' => false];
    }

    $headers[] = ['item_pic' => lang('Items.image'), 'sortable' => false];

    foreach ($definitionsWithTypes as $definitionId => $definitionInfo) {
        $headers[] = [$definitionId => $definitionInfo['name'], 'sortable' => true];
    }

    $headers[] = ['inventory' => '', 'escape' => false];
    $headers[] = ['stock' => '', 'escape' => false];

    return transform_headers($headers);
}

/**
 * Column set for exporting items: same data columns the manage table shows,
 * minus the icon/action columns (image thumbnail, inventory/stock/edit icons)
 * that only make sense in the live UI.
 *
 * @return array Array of single-key [column_key => label] pairs, see item_headers()
 */
function itemExportHeaders(): array
{
    $attribute = model(Attribute::class);
    $config = config(OSPOS::class)->settings;
    $definitionsWithTypes = $attribute->getDefinitionsByFlags($attribute::SHOW_IN_ITEMS, true);

    $headers = item_headers();

    if ($config['use_destination_based_tax']) {
        $headers[] = ['tax_percents' => lang('Items.tax_category')];
    } else {
        $headers[] = ['tax_percents' => lang('Items.tax_percents')];
    }

    foreach ($definitionsWithTypes as $definitionId => $definitionInfo) {
        $headers[] = [$definitionId => $definitionInfo['name'], 'type' => $definitionInfo['type']];
    }

    return $headers;
}

/**
 * Get the html data row for the item
 *
 * @param object $item
 * @param array $definitionNames Attribute definitions with types, keyed by definition_id (see Attribute::getDefinitionsByFlags(..., true))
 * @param array $taxPercentsByItemId Pre-computed tax percent strings, keyed by item_id (see Items::buildTaxPercentsByItem())
 * @return array
 */
function getItemDataRow(object $item, array $definitionNames, array $taxPercentsByItemId): array
{
    $config = config(OSPOS::class)->settings;

    $taxPercents = $taxPercentsByItemId[$item->item_id] ?? '-';

    $controller = get_controller();

    $image = null;
    if (!empty($item->pic_filename)) {
        $ext = pathinfo($item->pic_filename, PATHINFO_EXTENSION);

        $images = $ext == ''
            ? glob("./uploads/item_pics/$item->pic_filename.*")
            : glob("./uploads/item_pics/$item->pic_filename");

        if (sizeof($images) > 0) {
            $imagePath = ltrim($images[0], './');
            $image .= '<a class="rollover" href="' . base_url(implode('/', array_map('rawurlencode', explode('/', $imagePath)))) . '"><img alt="Image thumbnail" src="' . site_url('items/PicThumb/' . rawurlencode(pathinfo($images[0], PATHINFO_BASENAME))) . '"></a>';
        }
    }

    if ($config['multi_pack_enabled']) {
        $item->name .= NAME_SEPARATOR . $item->pack_name;
    }

    $columns = [
        'items.item_id' => $item->item_id,
        'item_number'   => $item->item_number,
        'name'          => $item->name,
        'category'      => $item->category,
        'company_name'  => $item->company_name,    // TODO: This isn't in the items table. Should this be here?
        'cost_price'    => to_currency($item->cost_price),
        'unit_price'    => to_currency($item->unit_price),
        'quantity'      => to_quantity_decimals($item->quantity),
        'tax_percents'  => !$taxPercents ? '-' : $taxPercents,
        'item_pic'      => $image
    ];

    $icons = [
        'inventory' => anchor(
            "$controller/inventory/$item->item_id",
            '<span class="glyphicon glyphicon-pushpin"></span>',
            [
                'class'           => 'modal-dlg',
                'data-btn-submit' => lang('Common.submit'),
                'title'           => lang(ucfirst($controller) . ".count")
            ]
        ),
        'stock'     => anchor(
            "$controller/countDetails/$item->item_id",
            '<span class="glyphicon glyphicon-list-alt"></span>',
            [
                'class' => 'modal-dlg',
                'title' => lang(ucfirst($controller) . ".details_count")
            ]
        ),
        'edit'      => anchor(
            "$controller/view/$item->item_id",
            '<span class="glyphicon glyphicon-edit"></span>',
            [
                'class'           => 'modal-dlg',
                'data-btn-submit' => lang('Common.submit'),
                'title'           => lang(ucfirst($controller) . ".update")
            ]
        )
    ];

    return $columns + expandAttributeValues($definitionNames, (array) $item) + $icons;
}

/**
 * Export row for an item: raw (unformatted) values, matching itemExportHeaders()'s
 * column set - no locale display formatting, no icons/HTML, since consumers (CSV/XLSX/etc
 * writers) each apply their own number formatting as appropriate for the target format.
 *
 * @param object $item
 * @param array $definitionNames Attribute definitions with types, keyed by definition_id (see Attribute::getDefinitionsByFlags(..., true))
 * @param array $taxPercentsByItemId Pre-computed tax percent strings, keyed by item_id (see Items::buildTaxPercentsByItem())
 * @return array
 */
function getItemExportRow(object $item, array $definitionNames, array $taxPercentsByItemId): array
{
    $config = config(OSPOS::class)->settings;

    $taxPercents = $taxPercentsByItemId[$item->item_id] ?? '';

    $name = $item->name;
    if ($config['multi_pack_enabled']) {
        $name .= NAME_SEPARATOR . $item->pack_name;
    }

    $columns = [
        'item_id'       => $item->item_id,
        'item_number'   => $item->item_number,
        'name'          => $name,
        'category'      => $item->category,
        'company_name'  => $item->company_name,
        'cost_price'    => $item->cost_price,
        'unit_price'    => $item->unit_price,
        'quantity'      => $item->quantity ?? '',
        'tax_percents'  => $taxPercents
    ];

    return $columns + expandAttributeValues($definitionNames, (array) $item, raw: true);
}

function giftcard_headers(): array
{
    return [
        ['giftcard_id'     => lang('Common.id')],
        ['last_name'       => lang('Common.last_name')],
        ['first_name'      => lang('Common.first_name')],
        ['giftcard_number' => lang('Giftcards.giftcard_number')],
        ['value'           => lang('Giftcards.card_value')]
    ];
}

/**
 * Get the header for the giftcard tabular view
 */
function get_giftcards_manage_table_headers(): string
{
    return transform_headers(giftcard_headers());
}

/**
 * Get the html data row for the giftcard
 */
function get_giftcard_data_row(object $giftcard): array
{
    $controller = get_controller();

    return [
        'giftcard_id'     => $giftcard->giftcard_id,
        'last_name'       => $giftcard->last_name,
        'first_name'      => $giftcard->first_name,
        'giftcard_number' => $giftcard->giftcard_number,
        'value'           => to_currency($giftcard->value),
        'edit'            => anchor(
            "$controller/view/$giftcard->giftcard_id",
            '<span class="glyphicon glyphicon-edit"></span>',
            [
                'class'           => 'modal-dlg',
                'data-btn-submit' => lang('Common.submit'),
                'title'           => lang(ucfirst($controller) . ".update")
            ]
        )
    ];
}

/**
 * Column set for exporting giftcards: giftcard_headers() minus the edit anchor column.
 */
function giftcardExportHeaders(): array
{
    return giftcard_headers();
}

/**
 * Export row for a giftcard: raw (unformatted) values, matching giftcardExportHeaders()'s column set.
 */
function getGiftcardExportRow(object $giftcard): array
{
    return [
        'giftcard_id'     => $giftcard->giftcard_id,
        'last_name'       => $giftcard->last_name,
        'first_name'      => $giftcard->first_name,
        'giftcard_number' => $giftcard->giftcard_number,
        'value'           => $giftcard->value
    ];
}

function item_kit_headers(): array
{
    return [
        ['item_kit_id'      => lang('Item_kits.kit')],
        ['item_kit_number'  => lang('Item_kits.item_kit_number')],
        ['name'             => lang('Item_kits.name')],
        ['description'      => lang('Item_kits.description')],
        ['total_cost_price' => lang('Items.cost_price'), 'sortable' => false],
        ['total_unit_price' => lang('Items.unit_price'), 'sortable' => false]
    ];
}

/**
 * Get the header for the item kits tabular view
 */
function get_item_kits_manage_table_headers(): string
{
    return transform_headers(item_kit_headers());
}

/**
 * Get the html data row for the item kit
 */
function get_item_kit_data_row(object $item_kit): array
{
    $controller = get_controller();

    return [
        'item_kit_id'      => $item_kit->item_kit_id,
        'item_kit_number'  => $item_kit->item_kit_number,
        'name'             => $item_kit->name,
        'description'      => $item_kit->description,
        'total_cost_price' => to_currency($item_kit->total_cost_price),
        'total_unit_price' => to_currency($item_kit->total_unit_price),
        'edit'             => anchor(
            "$controller/view/$item_kit->item_kit_id",
            '<span class="glyphicon glyphicon-edit"></span>',
            [
                'class'           => 'modal-dlg',
                'data-btn-submit' => lang('Common.submit'),
                'title'           => lang(ucfirst($controller) . ".update")
            ]
        )
    ];
}

/**
 * Column set for exporting item kits: item_kit_headers() minus the edit anchor column.
 */
function itemKitExportHeaders(): array
{
    return item_kit_headers();
}

/**
 * Export row for an item kit: raw (unformatted) values, matching itemKitExportHeaders()'s column set.
 */
function getItemKitExportRow(object $item_kit): array
{
    return [
        'item_kit_id'      => $item_kit->item_kit_id,
        'item_kit_number'  => $item_kit->item_kit_number,
        'name'             => $item_kit->name,
        'description'      => $item_kit->description,
        'total_cost_price' => $item_kit->total_cost_price,
        'total_unit_price' => $item_kit->total_unit_price
    ];
}

/**
 * @param array $columns
 * @param array $row
 * @return array
 */
function parse_attribute_values(array $columns, array $row): array
{
    $attribute_values = [];
    foreach ($columns as $column) {
        if (array_key_exists($column, $row)) {
            $attribute_value = explode('|', $row[$column]);
            $attribute_values = array_merge($attribute_values, $attribute_value);
        }
    }
    return $attribute_values;
}

/**
 * @param array $definitionNames Array of definition_id => ['name' => name, 'type' => type] or definition_id => name
 * @param array $row
 * @param bool $raw When true, skip locale formatting of DECIMAL attributes and return the raw value instead
 * @return array
 */
function expandAttributeValues(array $definitionNames, array $row, bool $raw = false): array
{
    $values = parse_attribute_values(['attribute_values', 'attribute_dtvalues', 'attribute_dvalues'], $row);

    $indexedValues = [];
    foreach ($values as $attributeValue) {
        $explodedValue = explode('_', $attributeValue);
        if (sizeof($explodedValue) > 1) {
            $indexedValues[$explodedValue[0]] = $explodedValue[1];
        }
    }

    $attributeValues = [];
    foreach ($definitionNames as $definitionId => $definitionInfo) {
        if (isset($indexedValues[$definitionId])) {
            $rawValue = $indexedValues[$definitionId];
            $isDecimal = is_array($definitionInfo) && isset($definitionInfo['type']) && $definitionInfo['type'] === DECIMAL;

            // Format DECIMAL attributes according to locale, unless the raw value was requested
            if ($isDecimal && !$raw) {
                $attributeValues["$definitionId"] = to_decimals($rawValue);
            } else {
                $attributeValues["$definitionId"] = $rawValue;
            }
        } else {
            $attributeValues["$definitionId"] = "";
        }
    }

    return $attributeValues;
}

function attribute_definition_headers(): array
{
    return [
        ['definition_id'    => lang('Attributes.definition_id')],
        ['definition_name'  => lang('Attributes.definition_name')],
        ['definition_type'  => lang('Attributes.definition_type')],
        ['definition_flags' => lang('Attributes.definition_flags')],
        ['definition_group' => lang('Attributes.definition_group')],
    ];
}

/**
 * @return string
 */
function get_attribute_definition_manage_table_headers(): string
{
    return transform_headers(attribute_definition_headers());
}

/**
 * @param object $attribute_row
 * @return array
 */
function get_attribute_definition_data_row(object $attribute_row): array
{

    $attribute = model(Attribute::class);
    $controller = get_controller();

    if (count($attribute->getDefinitionFlags()) == 0) {
        $definition_flags = lang('Common.none_selected_text');
    } elseif ($attribute->definition_type == GROUP) {
        $definition_flags = "-";
    } else {
        $definition_flags = implode(', ', $attribute_row->definition_flags);
    }

    return [
        'definition_id'    => $attribute_row->definition_id,
        'definition_name'  => $attribute_row->definition_name,
        'definition_type'  => $attribute_row->definition_type,
        'definition_group' => $attribute_row->definition_group,
        'definition_flags' => $definition_flags,
        'edit'             => anchor(
            "$controller/view/$attribute_row->definition_id",
            '<span class="glyphicon glyphicon-edit"></span>',
            [
                'class'           => 'modal-dlg',
                'data-btn-submit' => lang('Common.submit'),
                'title'           => lang(ucfirst($controller) . ".update")
            ]
        )
    ];
}

/**
 * Column set for exporting attribute definitions: attribute_definition_headers() minus the edit anchor column.
 */
function attributeExportHeaders(): array
{
    return attribute_definition_headers();
}

/**
 * Export row for an attribute definition: raw values, matching attributeExportHeaders()'s column set.
 * definition_flags is kept as the resolved human-readable label string (not the raw bitmask),
 * consistent with how the manage table displays it.
 */
function getAttributeExportRow(object $attribute_row): array
{
    $attribute = model(Attribute::class);

    if (count($attribute->getDefinitionFlags()) == 0) {
        $definition_flags = lang('Common.none_selected_text');
    } elseif ($attribute->definition_type == GROUP) {
        $definition_flags = "-";
    } else {
        $definition_flags = implode(', ', $attribute_row->definition_flags);
    }

    return [
        'definition_id'    => $attribute_row->definition_id,
        'definition_name'  => $attribute_row->definition_name,
        'definition_type'  => $attribute_row->definition_type,
        'definition_flags' => $definition_flags,
        'definition_group' => $attribute_row->definition_group
    ];
}

function expense_category_headers(): array
{
    return [
        ['expense_category_id'  => lang('Expenses_categories.category_id')],
        ['category_name'        => lang('Expenses_categories.name')],
        ['category_description' => lang('Expenses_categories.description')]
    ];
}

/**
 * Get the header for the expense categories tabular view
 */
function get_expense_category_manage_table_headers(): string
{
    return transform_headers(expense_category_headers());
}

/**
 * Gets the html data row for the expense category
 */
function get_expense_category_data_row(object $expense_category): array
{
    $controller = get_controller();

    return [
        'expense_category_id'  => $expense_category->expense_category_id,
        'category_name'        => $expense_category->category_name,
        'category_description' => $expense_category->category_description,
        'edit'                 => anchor(
            "$controller/view/$expense_category->expense_category_id",
            '<span class="glyphicon glyphicon-edit"></span>',
            [
                'class'           => 'modal-dlg',
                'data-btn-submit' => lang('Common.submit'),
                'title'           => lang(ucfirst($controller) . ".update")
            ]
        )
    ];
}

/**
 * Column set for exporting expense categories: expense_category_headers() minus the edit anchor column.
 */
function expenseCategoryExportHeaders(): array
{
    return expense_category_headers();
}

/**
 * Export row for an expense category: raw values, matching expenseCategoryExportHeaders()'s column set.
 */
function getExpenseCategoryExportRow(object $expense_category): array
{
    return [
        'expense_category_id'  => $expense_category->expense_category_id,
        'category_name'        => $expense_category->category_name,
        'category_description' => $expense_category->category_description
    ];
}

function expense_headers(): array
{
    return [
        ['expense_id'        => lang('Expenses.expense_id')],
        ['date'              => lang('Expenses.date')],
        ['supplier_name'     => lang('Expenses.supplier_name')],
        ['supplier_tax_code' => lang('Expenses.supplier_tax_code')],
        ['amount'            => lang('Expenses.amount')],
        ['tax_amount'        => lang('Expenses.tax_amount')],
        ['payment_type'      => lang('Expenses.payment')],
        ['category_name'     => lang('Expenses_categories.name')],
        ['description'       => lang('Expenses.description')],
        ['created_by'        => lang('Expenses.employee')]
    ];
}

/**
 * Get the header for the expenses tabular view
 */
function get_expenses_manage_table_headers(): string
{
    return transform_headers(expense_headers());
}

/**
 * Gets the html data row for the expenses
 */
function get_expenses_data_row(object $expense): array
{
    $controller = get_controller();

    return [
        'expense_id'        => $expense->expense_id,
        'date'              => to_datetime(strtotime($expense->date)),
        'supplier_name'     => $expense->supplier_name,
        'supplier_tax_code' => $expense->supplier_tax_code,
        'amount'            => to_currency($expense->amount),
        'tax_amount'        => to_currency($expense->tax_amount),
        'payment_type'      => $expense->payment_type,
        'category_name'     => $expense->category_name,
        'description'       => $expense->description,
        'created_by'        => $expense->first_name . ' ' . $expense->last_name,
        'edit'              => anchor(
            "$controller/view/$expense->expense_id",
            '<span class="glyphicon glyphicon-edit"></span>',
            [
                'class'           => 'modal-dlg',
                'data-btn-submit' => lang('Common.submit'),
                'title'           => lang(ucfirst($controller) . ".update")
            ]
        )
    ];
}

/**
 * Column set for exporting expenses: expense_headers() minus the edit anchor column.
 */
function expenseExportHeaders(): array
{
    return expense_headers();
}

/**
 * Export row for an expense: raw (unformatted) values, matching expenseExportHeaders()'s column set.
 */
function getExpenseExportRow(object $expense): array
{
    return [
        'expense_id'        => $expense->expense_id,
        'date'              => $expense->date,
        'supplier_name'     => $expense->supplier_name,
        'supplier_tax_code' => $expense->supplier_tax_code,
        'amount'            => $expense->amount,
        'tax_amount'        => $expense->tax_amount,
        'payment_type'      => $expense->payment_type,
        'category_name'     => $expense->category_name,
        'description'       => $expense->description,
        'created_by'        => $expense->first_name . ' ' . $expense->last_name
    ];
}

/**
 * Get the html data last row for the expenses
 */
function get_expenses_data_last_row(object $expense): array
{
    $table_data_rows = '';    // TODO: This variable is never used
    $sum_amount_expense = 0;
    $sum_tax_amount_expense = 0;

    foreach ($expense->getResult() as $key => $expense) {
        $sum_amount_expense += $expense->amount;
        $sum_tax_amount_expense += $expense->tax_amount;
    }

    return [
        'expense_id' => '-',
        'date'       => lang('Sales.total'),
        'amount'     => to_currency($sum_amount_expense),
        'tax_amount' => to_currency($sum_tax_amount_expense)
    ];
}

/**
 * Get the expense payments summary
 */
function get_expenses_manage_payments_summary(array $payments, ResultInterface $expenses): string    // TODO: $expenses is passed but never used.
{
    $table = '<div id="report_summary">';

    foreach ($payments as $key => $payment) {
        $amount = $payment['amount'];
        $table .= '<div class="summary_row">' . $payment['payment_type'] . ': ' . to_currency($amount) . '</div>';
    }

    $table .= '</div>';

    return $table;
}

function cashup_headers(): array
{
    return [
        ['cashup_id'            => lang('Cashups.id')],
        ['open_date'            => lang('Cashups.opened_date')],
        ['open_employee_id'     => lang('Cashups.open_employee')],
        ['open_amount_cash'     => lang('Cashups.open_amount_cash')],
        ['transfer_amount_cash' => lang('Cashups.transfer_amount_cash')],
        ['close_date'           => lang('Cashups.closed_date')],
        ['close_employee_id'    => lang('Cashups.close_employee')],
        ['closed_amount_cash'   => lang('Cashups.closed_amount_cash')],
        ['note'                 => lang('Cashups.note')],
        ['closed_amount_due'    => lang('Cashups.closed_amount_due')],
        ['closed_amount_card'   => lang('Cashups.closed_amount_card')],
        ['closed_amount_check'  => lang('Cashups.closed_amount_check')],
        ['closed_amount_total'  => lang('Cashups.closed_amount_total')]
    ];
}


/**
 * Get the header for the cashup tabular view
 */
function get_cashups_manage_table_headers(): string
{
    $headers = cashup_headers();

    return transform_headers($headers);
}

/**
 * Gets the html data row for the cashups
 */
function get_cash_up_data_row(object $cash_up): array
{
    $controller = get_controller();

    return [
        'cashup_id'            => $cash_up->cashup_id,
        'open_date'            => to_datetime(strtotime($cash_up->open_date)),
        'open_employee_id'     => $cash_up->open_first_name . ' ' . $cash_up->open_last_name,
        'open_amount_cash'     => to_currency($cash_up->open_amount_cash),
        'transfer_amount_cash' => to_currency($cash_up->transfer_amount_cash),
        'close_date'           => to_datetime(strtotime($cash_up->close_date)),
        'close_employee_id'    => $cash_up->close_first_name . ' ' . $cash_up->close_last_name,
        'closed_amount_cash'   => to_currency($cash_up->closed_amount_cash),
        'note'                 => $cash_up->note ? '<span class="glyphicon glyphicon-ok"></span>' : '<span class="glyphicon glyphicon-remove"></span>',
        'closed_amount_due'    => to_currency($cash_up->closed_amount_due),
        'closed_amount_card'   => to_currency($cash_up->closed_amount_card),
        'closed_amount_check'  => to_currency($cash_up->closed_amount_check),
        'closed_amount_total'  => to_currency($cash_up->closed_amount_total),
        'edit'                 => anchor(
            "$controller/view/$cash_up->cashup_id",
            '<span class="glyphicon glyphicon-edit"></span>',
            [
                'class'           => 'modal-dlg',
                'data-btn-submit' => lang('Common.submit'),
                'title'           => lang(ucfirst($controller) . ".update")
            ]
        )
    ];
}

/**
 * Column set for exporting cashups: cashup_headers() minus the edit anchor column.
 */
function cashupExportHeaders(): array
{
    return cashup_headers();
}

/**
 * Export row for a cashup: raw (unformatted) values, matching cashupExportHeaders()'s column set.
 * open_employee_id/close_employee_id keep the concatenated first/last name display values since
 * no raw employee ID is available in the row payload.
 */
function getCashupExportRow(object $cash_up): array
{
    return [
        'cashup_id'            => $cash_up->cashup_id,
        'open_date'            => $cash_up->open_date,
        'open_employee_id'     => $cash_up->open_first_name . ' ' . $cash_up->open_last_name,
        'open_amount_cash'     => $cash_up->open_amount_cash,
        'transfer_amount_cash' => $cash_up->transfer_amount_cash,
        'close_date'           => $cash_up->close_date,
        'close_employee_id'    => $cash_up->close_first_name . ' ' . $cash_up->close_last_name,
        'closed_amount_cash'   => $cash_up->closed_amount_cash,
        'note'                 => $cash_up->note ? 1 : 0,
        'closed_amount_due'    => $cash_up->closed_amount_due,
        'closed_amount_card'   => $cash_up->closed_amount_card,
        'closed_amount_check'  => $cash_up->closed_amount_check,
        'closed_amount_total'  => $cash_up->closed_amount_total
    ];
}

/**
 * Returns the right-most part of the controller name
 * @return string
 */
function get_controller(): string
{
    $router = service('router');
    $controller_name = strtolower($router->controllerName());
    $controller_name_parts = explode('\\', $controller_name);
    return end($controller_name_parts);
}

/**
 * Restores filter values from the URL query string.
 *
 * @param IncomingRequest $request The request object
 * @return array Array with 'start_date', 'end_date', and 'selected_filters' keys
 */
function restoreTableFilters(IncomingRequest $request): array
{
    $startDate = $request->getGet('start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $endDate = $request->getGet('end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $urlFilters = $request->getGet('filters', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    return array_filter([
        'start_date' => $startDate ?: null,
        'end_date' => $endDate ?: null,
        'selected_filters' => $urlFilters ?? []
    ], function ($value) {
        return $value !== null && $value !== [];
    });
}
