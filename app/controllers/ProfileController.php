<?php
declare(strict_types=1);

class ProfileController
{
    private CsrfService $csrf;
    private PhotoService $photoService;

    public function __construct()
    {
        $this->csrf = new CsrfService();
        $this->photoService = new PhotoService();
    }

    public function index(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM profiles WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => (int) $_SESSION['user_id']]);
        $profile = $stmt->fetch();
        $photos = $this->photoService->list((int) $_SESSION['user_id']);
        $csrfToken = $this->csrf->generateToken();
        $flash = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);
        $pageTitle = APP_NAME . ' — профиль';
        require __DIR__ . '/../views/profile/index.php';
    }

    public function update(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $token = $_POST['csrf_token'] ?? null;
        if (!$this->csrf->validateToken($token)) {
            http_response_code(419);
            echo 'Неверный CSRF токен';
            return;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'UPDATE profiles SET name = :name, age = :age, city = :city, goal = :goal, about = :about, mood = :mood WHERE user_id = :user_id'
        );
        $stmt->execute([
            'name' => trim($_POST['name'] ?? ''),
            'age' => $_POST['age'] !== '' ? (int) $_POST['age'] : null,
            'city' => trim($_POST['city'] ?? ''),
            'goal' => trim($_POST['goal'] ?? ''),
            'about' => trim($_POST['about'] ?? ''),
            'mood' => trim($_POST['mood'] ?? ''),
            'user_id' => (int) $_SESSION['user_id'],
        ]);

        $_SESSION['flash_success'] = 'Профиль обновлен.';
        header('Location: /profile');
        exit;
    }
}
