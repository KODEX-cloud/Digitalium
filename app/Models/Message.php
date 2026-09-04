<?php
namespace App\Models;

use App\Services\Database;

/**
 * Demandes entrantes — table `contact_messages`.
 *
 * La table servait à l'origine un formulaire de contact simple (nom, email,
 * message, trois statuts). Elle porte désormais le pipeline commercial :
 * qualification du besoin, organisation, budget, urgence, pièce jointe et
 * suivi par statuts. Les quatre méthodes historiques (`create`, `markRead`,
 * `archive`, `countNew`) sont conservées telles quelles — le formulaire simple
 * et l'ancien écran d'administration continuent de fonctionner.
 */
class Message extends Model {
    protected static string $table = 'contact_messages';

    /**
     * Étapes du pipeline, dans l'ordre. La clé est stockée en base, la valeur
     * est ce que voit l'administrateur. `archive` reste en fin de liste : ce
     * n'est pas une étape commerciale mais un rangement, et d'anciennes lignes
     * portent déjà cette valeur.
     */
    public const STATUTS = [
        'nouveau'      => 'Nouveau',
        'a_qualifier'  => 'À qualifier',
        'contacte'     => 'Contacté',
        'en_discussion'=> 'En discussion',
        'proposition'  => 'Proposition envoyée',
        'gagne'        => 'Gagné',
        'perdu'        => 'Perdu',
        'archive'      => 'Archivé',
    ];

    /** Statuts considérés comme « en cours de traitement ». */
    public const EN_COURS = ['a_qualifier', 'contacte', 'en_discussion', 'proposition'];

    /**
     * Colonnes acceptées à l'écriture. Tout ce qui n'est pas listé ici est
     * ignoré : une clé inattendue venant d'un POST ne peut pas atteindre la
     * base, même si un jour quelqu'un passe `$_POST` directement.
     */
    private const CHAMPS = [
        'nom', 'email', 'telephone', 'sujet', 'message', 'ip_address',
        'entreprise', 'secteur', 'pays', 'besoin', 'objectif', 'urgence',
        'budget', 'piece_jointe', 'piece_jointe_nom', 'source',
    ];

    public static function libelleStatut(?string $cle): string {
        return self::STATUTS[$cle ?? ''] ?? (string)($cle ?? '');
    }

    // ── Écriture ────────────────────────────────────────────────────────────

    /** Création historique — formulaire de contact simple. Inchangée. */
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

    /**
     * Création d'une demande commerciale complète.
     *
     * Les colonnes absentes de la table (installation pas encore migrée) sont
     * écartées avant l'INSERT : une demande incomplète vaut mieux qu'une
     * demande perdue.
     */
    public static function createLead(array $data): int {
        $cols = self::colonnesExistantes();
        $params = [];
        foreach (self::CHAMPS as $champ) {
            if (!in_array($champ, $cols, true)) { continue; }
            if (!array_key_exists($champ, $data)) { continue; }
            $params[$champ] = $data[$champ];
        }
        if (!isset($params['nom'], $params['email'], $params['message'])) {
            throw new \InvalidArgumentException('nom, email et message sont requis.');
        }

        $noms    = array_keys($params);
        $holders = ':' . implode(', :', $noms);
        $id = (int)Database::insert(
            "INSERT INTO contact_messages (" . implode(', ', $noms) . ", statut)
             VALUES ($holders, 'nouveau')",
            $params
        );

        self::journaliser($id, 'creation', null, 'nouveau', null, 'Formulaire public');
        return $id;
    }

    public static function markRead(int $id): void {
        Database::query("UPDATE contact_messages SET statut = 'a_qualifier' WHERE id = :id", ['id' => $id]);
    }

    public static function archive(int $id): void {
        Database::query("UPDATE contact_messages SET statut = 'archive' WHERE id = :id", ['id' => $id]);
    }

    /** Change le statut et journalise le passage. Retourne false si inchangé. */
    public static function changerStatut(int $id, string $statut, ?string $auteur = null): bool {
        if (!isset(self::STATUTS[$statut])) { return false; }
        $avant = self::find($id);
        $ancien = (string)($avant['statut'] ?? '');
        if ($ancien === $statut) { return false; }

        Database::query(
            "UPDATE contact_messages SET statut = :s WHERE id = :id",
            ['s' => $statut, 'id' => $id]
        );
        self::journaliser($id, 'statut', $ancien, $statut, null, $auteur);
        return true;
    }

    /** Ajoute une note interne, conservée dans l'historique. */
    public static function ajouterNote(int $id, string $note, ?string $auteur = null): bool {
        $note = trim($note);
        if ($note === '') { return false; }
        self::journaliser($id, 'note', null, null, $note, $auteur);
        return true;
    }

    // ── Lecture ─────────────────────────────────────────────────────────────

    /**
     * Recherche filtrée. Chaque filtre est facultatif ; les valeurs partent en
     * paramètres liés, jamais concaténées dans le SQL.
     *
     * @param array{statut?:string,secteur?:string,besoin?:string,q?:string} $f
     */
    public static function rechercher(array $f = [], int $limite = 200): array {
        $cols = self::colonnesExistantes();
        $where = [];
        $params = [];

        if (!empty($f['statut']) && isset(self::STATUTS[$f['statut']])) {
            $where[] = 'statut = :statut';
            $params['statut'] = $f['statut'];
        }
        foreach (['secteur', 'besoin'] as $champ) {
            if (!empty($f[$champ]) && in_array($champ, $cols, true)) {
                $where[] = "$champ = :$champ";
                $params[$champ] = $f[$champ];
            }
        }
        if (!empty($f['q'])) {
            $cibles = array_values(array_filter(
                ['nom', 'email', 'entreprise', 'sujet', 'message'],
                fn($c) => in_array($c, $cols, true)
            ));
            if ($cibles) {
                $ou = array_map(fn($c) => "$c LIKE :q", $cibles);
                $where[] = '(' . implode(' OR ', $ou) . ')';
                $params['q'] = '%' . $f['q'] . '%';
            }
        }

        $sql = "SELECT * FROM contact_messages"
             . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
             . ' ORDER BY created_at DESC, id DESC LIMIT ' . max(1, min(1000, $limite));
        return Database::fetchAll($sql, $params);
    }

    /** Valeurs distinctes d'une colonne, pour alimenter un menu de filtre. */
    public static function valeursDistinctes(string $champ): array {
        if (!in_array($champ, ['secteur', 'besoin', 'pays'], true)) { return []; }
        if (!in_array($champ, self::colonnesExistantes(), true)) { return []; }
        $rows = Database::fetchAll(
            "SELECT DISTINCT $champ AS v FROM contact_messages
             WHERE $champ IS NOT NULL AND $champ <> '' ORDER BY $champ ASC"
        );
        return array_column($rows, 'v');
    }

    /** Historique d'une demande, du plus ancien au plus récent. */
    public static function historique(int $id): array {
        try {
            return Database::fetchAll(
                "SELECT * FROM message_events WHERE message_id = :id ORDER BY id ASC",
                ['id' => $id]
            );
        } catch (\Throwable $e) {
            return [];   // table pas encore créée : l'écran reste utilisable
        }
    }

    public static function countNew(): int {
        $row = Database::fetch("SELECT COUNT(*) as total FROM contact_messages WHERE statut = 'nouveau'");
        return (int)($row['total'] ?? 0);
    }

    /** Compteurs du tableau de bord. Ne lève jamais : renvoie des zéros. */
    public static function statistiques(): array {
        $z = ['nouveaux' => 0, 'en_cours' => 0, 'semaine' => 0, 'gagnes' => 0];
        try {
            $enCours = "'" . implode("','", self::EN_COURS) . "'";
            $row = Database::fetch(
                "SELECT
                    SUM(statut = 'nouveau')                                        AS nouveaux,
                    SUM(statut IN ($enCours))                                      AS en_cours,
                    SUM(created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY))             AS semaine,
                    SUM(statut = 'gagne')                                          AS gagnes
                 FROM contact_messages"
            );
            foreach ($z as $k => $_) { $z[$k] = (int)($row[$k] ?? 0); }
        } catch (\Throwable $e) { /* table absente : zéros */ }
        return $z;
    }

    /**
     * Nombre de demandes venues de cette adresse IP depuis N minutes.
     * Sert à limiter les envois abusifs sans table ni cache supplémentaire :
     * ce qu'on veut plafonner est précisément ce que cette table enregistre.
     */
    public static function envoisRecents(string $ip, int $minutes = 60): int {
        if ($ip === '') { return 0; }
        // MySQL refuse un paramètre lié dans INTERVAL quand PDO n'émule pas les
        // requêtes préparées (c'est le cas ici, Database.php:28). La valeur est
        // donc bornée puis intégrée comme entier — jamais une entrée utilisateur.
        $minutes = max(1, min(1440, $minutes));
        try {
            $row = Database::fetch(
                "SELECT COUNT(*) AS n FROM contact_messages
                 WHERE ip_address = :ip AND created_at >= DATE_SUB(NOW(), INTERVAL $minutes MINUTE)",
                ['ip' => $ip]
            );
            return (int)($row['n'] ?? 0);
        } catch (\Throwable $e) {
            return 0;   // en cas de doute, on laisse passer plutôt que de bloquer un vrai prospect
        }
    }

    // ── Interne ─────────────────────────────────────────────────────────────

    private static function journaliser(int $id, string $type, ?string $ancien,
                                        ?string $nouveau, ?string $note, ?string $auteur): void {
        try {
            Database::insert(
                "INSERT INTO message_events (message_id, type, ancien, nouveau, note, auteur)
                 VALUES (:m, :t, :a, :n, :note, :auteur)",
                ['m' => $id, 't' => $type, 'a' => $ancien, 'n' => $nouveau,
                 'note' => $note, 'auteur' => $auteur]
            );
        } catch (\Throwable $e) {
            // L'historique est un confort : son indisponibilité ne doit jamais
            // faire échouer l'enregistrement d'une demande commerciale.
        }
    }

    /** Colonnes réellement présentes, mises en cache pour la requête en cours. */
    private static ?array $colonnes = null;
    private static function colonnesExistantes(): array {
        if (self::$colonnes !== null) { return self::$colonnes; }
        try {
            $rows = Database::fetchAll("SHOW COLUMNS FROM contact_messages");
            self::$colonnes = array_column($rows, 'Field');
        } catch (\Throwable $e) {
            self::$colonnes = ['nom', 'email', 'telephone', 'sujet', 'message', 'ip_address'];
        }
        return self::$colonnes;
    }
}
