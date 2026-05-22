<?php
/**
 * @var array $config
 * @var string $companyName
 * @var string $companyDetails
 */

helper('url');
?>

<!doctype html>
<html lang="<?= esc(service('request')->getLocale()) ?>">
<head>
    <meta charset="utf-8">
    <title><?= lang('Sales.customer_display') ?></title>
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('images/favicon.ico') ?>">
    <link rel="stylesheet" href="<?= base_url('resources/bootswatch/' . (empty($config['theme']) ? 'flatly' : esc($config['theme'])) . '/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('resources/opensourcepos-8e34d6a398.min.css') ?>">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: #f8f8f8;
            color: #333;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        }

        body {
            width: 100%;
            overflow: hidden;
        }

        .customer-display-header {
            background: #1f3143;
            color: #fff;
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.02em;
            padding: 6px 12px;
            border-bottom: 1px solid #102131;
        }

        .customer-display-shell {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 12px 18px 18px;
            box-sizing: border-box;
        }

        .customer-display-company {
            text-align: center;
            margin-bottom: 18px;
        }

        .customer-display-company img {
            display: block;
            margin: 0 auto 6px;
            max-height: 84px;
            max-width: 240px;
        }

        .customer-display-company .company-name {
            font-size: 20px;
            font-weight: 600;
            line-height: 1.2;
            margin-top: 12px;
        }

        .customer-display-company .company-details {
            font-size: 13px;
            line-height: 1.35;
            white-space: pre-line;
        }

        .customer-display-company .company-phone {
            font-size: 13px;
            line-height: 1.35;
            margin-top: 4px;
        }

        .customer-display-main-row {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            margin-top: 6px;
        }

        .customer-display-cart-column {
            flex: 1 1 auto;
            min-width: 0;
        }

        .customer-display-summary-column {
            flex: 0 0 320px;
            width: 320px;
        }

        .customer-display-summary-panel,
        .customer-display-info-panel,
        .customer-display-items-panel {
            margin-bottom: 0;
        }

        .customer-display-summary-panel .panel-heading,
        .customer-display-info-panel .panel-heading,
        .customer-display-items-panel .panel-heading {
            font-weight: 600;
        }

        .customer-display-summary-panel .table,
        .customer-display-info-table {
            margin-bottom: 0;
            font-size: 13px;
        }

        .customer-display-summary-panel .table > tbody > tr > th,
        .customer-display-info-table > tbody > tr > th {
            background: #f8fbfd;
            width: 56%;
            font-weight: 700;
        }

        .customer-display-summary-panel .table > tbody > tr > td,
        .customer-display-info-table > tbody > tr > td {
            width: 44%;
            text-align: right;
            white-space: nowrap;
            font-weight: 600;
        }

        .customer-display-summary-panel .rate-row th,
        .customer-display-summary-panel .rate-row td {
            color: #c00000;
        }

        .customer-display-summary-panel .summary-section-row th {
            background: #eaf2f8;
            color: #1f3b5b;
            font-weight: 700;
        }

        .customer-display-summary-panel .summary-subtable {
            width: 100%;
        }

        .customer-display-summary-panel .summary-subtable > tbody > tr > th {
            background: #fdfefe;
            font-weight: 600;
        }

        .customer-display-summary-panel .summary-subtable > tbody > tr > td {
            font-weight: 600;
        }

        .register-wrap {
            width: 100%;
        }

        #register {
            width: 100%;
            margin: 0;
            table-layout: fixed;
            background: #fff;
        }

        #register th,
        #register td {
            text-align: center;
            vertical-align: middle;
            padding: 6px 5px;
            word-wrap: break-word;
        }

        #register thead th {
            font-size: 12px;
            font-weight: 600;
            color: #333;
        }

        #register tbody td {
            font-size: 15px;
        }

        #register tbody td.item-name-cell {
            font-size: 16px;
            text-align: left;
        }

        #register tbody td.price-cell {
            font-size: 15px;
        }

        #register tbody td.serial-cell {
            font-size: 12px;
            color: #2F4F4F;
        }

        .customer-display-summary-panel .table > tbody > tr > th,
        .customer-display-info-table > tbody > tr > th {
            border-top: 1px solid #e5e5e5;
        }

        .customer-display-summary-panel .table > tbody > tr > td,
        .customer-display-info-table > tbody > tr > td {
            border-top: 1px solid #e5e5e5;
        }

        .customer-display-summary-panel .panel-body,
        .customer-display-info-panel .panel-body,
        .customer-display-items-panel .panel-body {
            padding: 12px 15px;
        }

        .customer-display-summary-column .panel-body {
            padding-top: 8px;
        }

        .customer-display-summary-column .customer-name-value,
        .customer-display-summary-column .giftcard-value,
        .customer-display-summary-column .reward-value {
            text-align: right;
        }

        .customer-display-footer {
            margin-top: 14px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="customer-display-header">Open Source Point of Sale</div>
    <div class="customer-display-shell">
        <div class="customer-display-company">
            <?php if (!empty($config['company_logo'])) { ?>
                <img src="<?= base_url('uploads/' . esc($config['company_logo'], 'url')) ?>" alt="company_logo">
            <?php } ?>
            <div class="company-name"><?= esc($companyName) ?></div>
            <div class="company-phone">Phone: <?= esc((string)($config['phone'] ?? '')) ?></div>
            <?php if ($companyDetails !== '') { ?>
                <div class="company-details"><?= nl2br(esc($companyDetails)) ?></div>
            <?php } ?>
        </div>

        <div class="customer-display-main-row">

