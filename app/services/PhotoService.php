<?php
declare(strict_types=1);

class PhotoService
{
    public function store(int $userId, array $file): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = bin2hex(random_bytes(8)) . '.' . strtolower($extension);
        $targetDir = __DIR__ . '/../../storage/uploads';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        $targetPath = $targetDir . '/' . $safeName;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return null;
        }

        $relativePath = '/storage/uploads/' . $safeName;
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO profile_photos (user_id, path) VALUES (:user_id, :path)');
        $stmt->execute(['user_id' => $userId, 'path' => $relativePath]);
        return $relativePath;
    }

    public function list(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT path FROM profile_photos WHERE user_id = :user_id ORDER BY id DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
