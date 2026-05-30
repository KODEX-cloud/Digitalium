<?php
namespace App\Models;

use App\Services\Database;

class Media extends Model {
    protected static string $table = 'media';

    /**
     * Add an uploaded file into the media library DB.
     */
    public static function add(array $data): string {
        $sql = "INSERT INTO " . static::$table . " (filename, filepath, original_name, file_size, mime_type) 
                VALUES (:filename, :filepath, :original_name, :file_size, :mime_type)";
        return Database::insert($sql, [
            'filename'      => $data['filename'],
            'filepath'      => $data['filepath'],
            'original_name' => $data['original_name'],
            'file_size'     => (int)$data['file_size'],
            'mime_type'     => $data['mime_type']
        ]);
    }

    /**
     * Delete file from disk and DB.
     */
    public static function deleteMedia(int $id): bool {
        $media = self::find($id);
        if ($media) {
            $physicalPath = PUBLIC_PATH . $media['filepath'];
            if (file_exists($physicalPath)) {
                @unlink($physicalPath);
            }
            
            $sql = "DELETE FROM " . static::$table . " WHERE id = :id";
            Database::query($sql, ['id' => $id]);
            return true;
        }
        return false;
    }

    /**
     * Search media by original or safe filename.
     */
    public static function search(string $query): array {
        $sql = "SELECT * FROM " . static::$table . " 
                WHERE original_name LIKE :query OR filename LIKE :query 
                ORDER BY id DESC";
        return Database::fetchAll($sql, ['query' => '%' . $query . '%']);
    }
}
