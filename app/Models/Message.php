<?php
namespace App\Models;

use App\Services\Database;

class Message extends Model {
    protected static string $table = 'contact_messages';

    public static function create(array $data): string {
        return Database::insert(
            "INSERT INTO contact_messages (nom, email, telephone, sujet, message, ip_address, statut)
             VALUES (:nom, :email, :telephone, :sujet, :message, :ip_address, 'nouveau')",
            [
                'nom'        => $data['nom'],
                'email'      => $data['email'],
                'telephone'  => $data['telephone'] ?? null,
                'sujet'      => $data['sujet'] ?? null,
                'message'    => $data['message'],
                'ip_address' => $data['ip_address'] ?? null,
            ]
        );
    }

    public static function markRead(int $id): void {
        Database::query("UPDATE contact_messages SET statut = 'lu' WHERE id = :id", ['id' => $id]);
    }

    public static function archive(int $id): void {
        Database::query("UPDATE contact_messages SET statut = 'archivé' WHERE id = :id", ['id' => $id]);
    }

    public static function countNew(): int {
        $row = Database::fetch("SELECT COUNT(*) as total FROM contact_messages WHERE statut = 'nouveau'");
        return (int)($row['total'] ?? 0);
    }
}
