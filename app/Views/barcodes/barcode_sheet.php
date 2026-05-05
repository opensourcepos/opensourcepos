<?php
/**
 * @var array $barcode_config
 * @var array $items
 */

use App\Libraries\Barcode_lib;

$barcode_lib = new Barcode_lib();
?>

<!doctype html>
<html lang="<?= current_language_code() ?>">
    <head>
        <meta charset="utf-8">
        <title><?= esc(lang('Items.generate_barcodes')) ?></title>
        <link rel="stylesheet" href="<?= esc(base_url('css/barcode_font.css'), 'url') ?>">
        <style>
            .barcode svg {
                height: <?= (int) $barcode_config['barcode_height'] ?>px;
                width: <?= (int) $barcode_config['barcode_width'] ?>px;
            }
        </style>
    </head>
    <body class="<?= esc('font_' . $barcode_lib->get_font_name($barcode_config['barcode_font']), 'attr') ?>" style="font-size: <?= (int) $barcode_config['barcode_font_size'] ?>px;">
        <table style="border-spacing: <?= (int) $barcode_config['barcode_page_cellspacing'] ?>px; width: <?= (int) $barcode_config['barcode_page_width'] ?>%;">
            <tr>
                <?php
                    $count = 0;
                    foreach ($items as $item) {
                        if ($count % $barcode_config['barcode_num_in_row'] == 0 && $count != 0) {
                            echo '</tr><tr>';
                        }
                        echo '<td>' . $barcode_lib->display_barcode($item, $barcode_config) . '</td>';
                        $count++;
                    }
                ?>
            </tr>
        </table>
    </body>
</html>
