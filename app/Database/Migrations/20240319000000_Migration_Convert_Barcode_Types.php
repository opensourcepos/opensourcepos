<?php

namespace App\Database\Migrations;

use App\Models\Appconfig;
use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;
use Config\OSPOS;

class Migration_Convert_Barcode_Types extends Migration
{
    private Appconfig $appconfig;
    private array $config;

    public function __construct(?Forge $forge = null)
    {
        $this->appconfig = model(Appconfig::class);
        $this->config = config(OSPOS::class)->settings;

        parent::__construct($forge);
    }

    /**
     * Perform a migration step.
     */
    public function up(): void
    {

        $old_barcode_type = $this->config['barcode_type'];

        switch ($old_barcode_type) {
            case 'Code39':
                $new_barcode_type = 'C39';
                break;
            case 'Ean13':
                $new_barcode_type = 'EAN13';
                break;
            case 'Ean8':
                $new_barcode_type = 'EAN8';
                break;
            default:
            case 'Code128':
                $new_barcode_type = 'C128';
                break;
        }

        $this->appconfig->save(['barcode_type' => $new_barcode_type]);
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
        $new_barcode_type = $this->config['barcode_type'];

        switch ($new_barcode_type) {
            case 'C39':
                $old_barcode_type = 'Code39';
                break;
            case 'EAN13':
                $old_barcode_type = 'Ean13';
                break;
            case 'EAN8':
                $old_barcode_type = 'Ean8';
                break;
            default:
            case 'C128':
                $old_barcode_type = 'Code128';
                break;
        }

        $this->appconfig->save(['barcode_type' => $old_barcode_type]);
    }
}
