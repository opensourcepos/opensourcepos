<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration to sanitize existing image filenames by replacing spaces with underscores
 * This fixes issue #4372 where thumbnails failed to load for images with spaces in filenames
 */
class FixImageFilenameSpaces extends Migration
{
    /**
     * Perform a migration.
     */
    public function up(): void
    {
        $db = \Config\Database::connect();
        $builder = $db->table('ospos_items');
        
        // Get all items with pic_filename containing spaces
        $query = $builder->like('pic_filename', ' ', 'both')->get();
        $items = $query->getResult();
        
        foreach ($items as $item) {
            $old_filename = $item->pic_filename;
            $ext = pathinfo($old_filename, PATHINFO_EXTENSION);
            $base_name = pathinfo($old_filename, PATHINFO_FILENAME);
            
            // Sanitize the filename by replacing spaces and special characters
            $sanitized_name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $base_name);
            $new_filename = $sanitized_name . '.' . $ext;
            
            // Rename the file on the filesystem
            $old_path = FCPATH . 'uploads/item_pics/' . $old_filename;
            $new_path = FCPATH . 'uploads/item_pics/' . $new_filename;
            
            if (file_exists($old_path)) {
                // Rename the original file
                if (rename($old_path, $new_path)) {
                    // Check if thumbnail exists and rename it too
                    $old_thumb = FCPATH . 'uploads/item_pics/' . $base_name . '_thumb.' . $ext;
                    $new_thumb = FCPATH . 'uploads/item_pics/' . $sanitized_name . '_thumb.' . $ext;
                    if (file_exists($old_thumb)) {
                        rename($old_thumb, $new_thumb);
                    }
                    
                    // Update database record
                    $builder->where('item_id', $item->item_id)
                        ->update(['pic_filename' => $new_filename]);
                }
            }
        }
    }

    /**
     * Revert a migration.
     * Note: This migration does not support rollback as the original filenames are lost
     */
    public function down(): void
    {
        // This migration cannot be safely reversed as the original filenames are lost
        // after sanitization.
    }
}