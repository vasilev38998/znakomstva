<?php
declare(strict_types=1);

class VerificationController
{
    private CsrfService $csrf;

    public function __construct()
    {
        $this->csrf = new CsrfService();
    }

    public function index(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT status, code_phrase FROM selfie_verifications WHERE user_id = :user_id ORDER BY id DESC LIMIT 1');
        $stmt->execute(['user_id' => (int) $_SESSION['user_id']]);
        $verification = $stmt->fetch();
        $csrfToken = $this->csrf->generateToken();
        $flash = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);
        $pageTitle = APP_NAME . ' — верификация';
        require __DIR__ . '/../views/verification/index.php';
    }

    public function submit(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $token = $_POST['csrf_token'] ?? null;
        if (!$this->csrf->validateToken($token)) {
            http_response_code(419);
            echo 'Неверный CSRF токен';
            return;
        }

        $codePhrase = trim($_POST['code_phrase'] ?? '');
        if ($codePhrase === '') {
            header('Location: ' . BASE_URL . 'verification');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO selfie_verifications (user_id, code_phrase, status) VALUES (:user_id, :code_phrase, :status)');
        $stmt->execute([
            'user_id' => (int) $_SESSION['user_id'],
            'code_phrase' => $codePhrase,
            'status' => 'pending',
        ]);

        $_SESSION['flash_success'] = 'Заявка отправлена. Мы проверим селфи в ближайшее время.';
        header('Location: ' . BASE_URL . 'verification');
        exit;
    }
}
