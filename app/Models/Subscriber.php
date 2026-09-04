<?php
namespace App\Models;

use App\Services\Database;

/**
 * Abonnés à la newsletter.
 *
 * Table dédiée plutôt que réemploi de `contact_messages` : un abonné n'est pas
 * une demande commerciale. Les mélanger polluerait le pipeline de leads et
 * rendrait impossible un désabonnement propre.
 *
 * Un désabonnement passe le statut à 'unsubscribed' sans effacer la ligne :
 * réinscrire quelqu'un qui s'est désabonné doit rester un acte volontaire.
 */
class Subscriber extends Model {
    protected static string $table = 'newsletter_subscribers';

    public const STATUTS = [
        'active'       => 'Abonné',
        'unsubscribed' => 'Désabonné',
    ];

    /**
     * Enregistre un abonné.
     *
     * @return string 'nouveau' | 'existant' | 'reactive'
     */
    public static function inscrire(string $email, string $source = '', string $ip = ''): string {
        $email = strtolower(trim($email));

        $existant = Database::fetch(
            "SELECT id, status FROM newsletter_subscribers WHERE email = :e LIMIT 1",
            ['e' => $email]
        );

        if ($existant) {
            if (($existant['status'] ?? '') === 'active') { return 'existant'; }
            Database::query(
                "UPDATE newsletter_subscribers SET status = 'active' WHERE id = :id",
                ['id' => (int)$existant['id']]
            );
            return 'reactive';
        }

        Database::insert(
            "INSERT INTO newsletter_subscribers (email, source, ip_address, status)
             VALUES (:e, :s, :ip, 'active')",
            ['e' => $email, 's' => substr($source, 0, 100), 'ip' => substr($ip, 0, 45)]
        );
        return 'nouveau';
    }

    /** Liste filtrée, la plus récente d'abord. */
    public static function rechercher(string $statut = '', string $q = '', int $limite = 500): array {
        $limite = max(1, min(2000, $limite));
        $where  = [];
        $params = [];

        if (isset(self::STATUTS[$statut])) {
            $where[] = 'status = :st';
            $params['st'] = $statut;
        }
        $q = trim($q);
        if ($q !== '') {
            $where[] = '(email LIKE :q OR source LIKE :q)';
            $params['q'] = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
        }

        $sql = "SELECT * FROM newsletter_subscribers"
             . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
             . " ORDER BY created_at DESC, id DESC LIMIT $limite";
        return Database::fetchAll($sql, $params);
    }

    public static function statistiques(): array {
        $ligne = Database::fetch(
            "SELECT
               COUNT(*)                                                        AS total,
               SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END)              AS actifs,
               SUM(CASE WHEN status = 'unsubscribed' THEN 1 ELSE 0 END)        AS desabonnes,
               SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS mois
             FROM newsletter_subscribers"
        );
        return [
            'total'      => (int)($ligne['total'] ?? 0),
            'actifs'     => (int)($ligne['actifs'] ?? 0),
            'desabonnes' => (int)($ligne['desabonnes'] ?? 0),
            'mois'       => (int)($ligne['mois'] ?? 0),
        ];
    }

    public static function changerStatut(int $id, string $statut): bool {
        if (!isset(self::STATUTS[$statut])) { return false; }
        Database::query(
            "UPDATE newsletter_subscribers SET status = :s WHERE id = :id",
            ['s' => $statut, 'id' => $id]
        );
        return true;
    }

    /**
     * Nombre d'inscriptions récentes depuis une IP — limitation anti-abus.
     *
     * Les minutes sont bornées puis écrites en clair : PDO tourne sans émulation
     * des requêtes préparées, où MySQL refuse un paramètre lié dans un INTERVAL.
     */
    public static function envoisRecents(string $ip, int $minutes = 60): int {
        if (trim($ip) === '') { return 0; }
        $minutes = max(1, min(1440, $minutes));
        $ligne = Database::fetch(
            "SELECT COUNT(*) AS n FROM newsletter_subscribers
             WHERE ip_address = :ip AND created_at >= DATE_SUB(NOW(), INTERVAL $minutes MINUTE)",
            ['ip' => $ip]
        );
        return (int)($ligne['n'] ?? 0);
    }
}
