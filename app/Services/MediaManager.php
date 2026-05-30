<?php
namespace App\Services;

use Exception;

class MediaManager {
    private static array $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg'
    ];

    private static int $maxFileSize = 10 * 1024 * 1024; // 10MB

    /**
     * Upload an image from $_FILES securely.
     */
    public static function upload(array $file): array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception(self::getUploadErrorMessage($file['error']));
        }

        if ($file['size'] > self::$maxFileSize) {
            throw new Exception("Le fichier est trop volumineux. Taille max : 10 Mo.");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!array_key_exists($mimeType, self::$allowedMimeTypes)) {
            throw new Exception("Format de fichier non supporté. Formats autorisés : JPG, PNG, GIF, WEBP, SVG.");
        }

        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0755, true);
        }

        $extension = self::$allowedMimeTypes[$mimeType];
        $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
        $safeName = self::slugify($baseName);
        $uniqueName = $safeName . '-' . time() . '.' . $extension;
        $destination = UPLOAD_PATH . '/' . $uniqueName;

        $finalFilename = $uniqueName;
        $finalMime = $mimeType;
        $finalDestination = $destination;

        if (function_exists('imagecreatefromstring') && in_array($extension, ['jpg', 'png', 'webp'])) {
            try {
                $finalFilename = $safeName . '-' . time() . '.webp';
                $finalDestination = UPLOAD_PATH . '/' . $finalFilename;
                $finalMime = 'image/webp';

                if (self::convertToWebp($file['tmp_name'], $finalDestination)) {
                    $finalSize = filesize($finalDestination);
                } else {
                    $finalFilename = $uniqueName;
                    $finalDestination = $destination;
                    $finalMime = $mimeType;
                    if (!move_uploaded_file($file['tmp_name'], $finalDestination)) {
                        throw new Exception("Impossible de déplacer le fichier vers le répertoire de destination.");
                    }
                    $finalSize = $file['size'];
                }
            } catch (Exception $e) {
                $finalFilename = $uniqueName;
                $finalDestination = $destination;
                $finalMime = $mimeType;
                if (!move_uploaded_file($file['tmp_name'], $finalDestination)) {
                    throw new Exception("Impossible de déplacer le fichier vers le répertoire de destination.");
                }
                $finalSize = $file['size'];
            }
        } else {
            if (!move_uploaded_file($file['tmp_name'], $finalDestination)) {
                throw new Exception("Impossible de déplacer le fichier vers le répertoire de destination.");
            }
            $finalSize = $file['size'];
        }

        return [
            'filename'      => $finalFilename,
            'filepath'      => UPLOAD_URL . '/' . $finalFilename,
            'original_name' => $file['name'],
            'file_size'     => $finalSize,
            'mime_type'     => $finalMime
        ];
    }

    /**
     * Convert an image to WebP format.
     */
    private static function convertToWebp(string $sourcePath, string $destinationPath, int $quality = 82): bool {
        $imageString = file_get_contents($sourcePath);
        $image = imagecreatefromstring($imageString);
        if (!$image) {
            return false;
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $result = imagewebp($image, $destinationPath, $quality);
        imagedestroy($image);
        return $result;
    }

    /**
     * Delete a file safely.
     */
    public static function delete(string $filename): bool {
        $filePath = UPLOAD_PATH . '/' . basename($filename);
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    /**
     * Generate safe slug.
     */
    private static function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }

    /**
     * Map PHP upload errors to message.
     */
    private static function getUploadErrorMessage(int $code): string {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE   => "Le fichier dépasse la taille autorisée par le serveur.",
            UPLOAD_ERR_FORM_SIZE  => "Le fichier dépasse la taille autorisée par le formulaire.",
            UPLOAD_ERR_PARTIAL    => "Le fichier n'a été que partiellement téléversé.",
            UPLOAD_ERR_NO_FILE    => "Aucun fichier n'a été téléversé.",
            UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant.",
            UPLOAD_ERR_CANT_WRITE => "Échec d'écriture du fichier sur le disque.",
            UPLOAD_ERR_EXTENSION  => "Une extension PHP a arrêté le téléversement.",
            default               => "Erreur inconnue lors du téléversement."
        };
    }
}
