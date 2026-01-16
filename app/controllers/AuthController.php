<?php
declare(strict_types=1);

class AuthController
{
    private CsrfService $csrf;
    private RateLimiter $rateLimiter;

    public function __construct()
    {
        $this->csrf = new CsrfService();
        $this->rateLimiter = new RateLimiter(__DIR__ . '/../../storage');
    }

    public function login(): void
    {
        $pageTitle = APP_NAME . ' — вход';
        $csrfToken = $this->csrf->generateToken();
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);
        require __DIR__ . '/../views/auth/login.php';
    }

    public function handleLogin(): void
    {
        $this->guardCsrf();
        $identifier = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($this->rateLimiter->tooManyAttempts('login:' . $this->ip(), 5, 60)) {
            $this->flashError('Слишком много попыток. Попробуйте позже.');
            $this->redirect('/login');
            return;
        }

        if ($identifier === '' || $password === '') {
            $this->flashError('Введите email и пароль.');
            $this->redirect('/login');
            return;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, password_hash, email_verified_at FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $identifier]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->flashError('Неверный email или пароль.');
            $this->redirect('/login');
            return;
        }

        if (empty($user['email_verified_at'])) {
            $this->flashError('Подтвердите email перед входом.');
            $this->redirect('/login');
            return;
        }

        $_SESSION['user_id'] = (int) $user['id'];
        $this->redirect('/');
    }

    public function register(): void
    {
        $pageTitle = APP_NAME . ' — регистрация';
        $csrfToken = $this->csrf->generateToken();
        $error = $_SESSION['flash_error'] ?? null;
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
        require __DIR__ . '/../views/auth/register.php';
    }

    public function handleRegister(): void
    {
        $this->guardCsrf();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $name = trim($_POST['name'] ?? '');

        if ($this->rateLimiter->tooManyAttempts('register:' . $this->ip(), 3, 300)) {
            $this->flashError('Слишком много попыток. Попробуйте позже.');
            $this->redirect('/register');
            return;
        }

        if ($email === '' || $password === '' || $name === '') {
            $this->flashError('Заполните все поля.');
            $this->redirect('/register');
            return;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $this->flashError('Этот email уже используется.');
            $this->redirect('/register');
            return;
        }

        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (:email, :hash)');
        $stmt->execute([
            'email' => $email,
            'hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        $userId = (int) $pdo->lastInsertId();

        $stmt = $pdo->prepare('INSERT INTO profiles (user_id, name) VALUES (:user_id, :name)');
        $stmt->execute(['user_id' => $userId, 'name' => $name]);

        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare('INSERT INTO email_verifications (user_id, token, expires_at) VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 1 DAY))');
        $stmt->execute(['user_id' => $userId, 'token' => $token]);
        $pdo->commit();

        $_SESSION['flash_success'] = 'Проверьте email. Для демо используйте ссылку подтверждения ниже.';
        $_SESSION['verification_link'] = BASE_URL . 'verify?token=' . $token;
        $this->redirect('/register');
    }

    public function verify(): void
    {
        $token = $_GET['token'] ?? '';
        if ($token === '') {
            $this->redirect('/');
            return;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, user_id FROM email_verifications WHERE token = :token AND expires_at > NOW() LIMIT 1');
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch();
        if (!$row) {
            $this->flashError('Ссылка подтверждения недействительна.');
            $this->redirect('/register');
            return;
        }

        $pdo->beginTransaction();
        $stmt = $pdo->prepare('UPDATE users SET email_verified_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $row['user_id']]);
        $stmt = $pdo->prepare('DELETE FROM email_verifications WHERE id = :id');
        $stmt->execute(['id' => $row['id']]);
        $pdo->commit();

        $_SESSION['flash_success'] = 'Email подтвержден. Теперь можно войти.';
        $this->redirect('/login');
    }

    public function logout(): void
    {
        $this->guardCsrf();
        session_destroy();
        $this->redirect('/');
    }

    private function guardCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!$this->csrf->validateToken($token)) {
            http_response_code(419);
            echo 'Неверный CSRF токен';
            exit;
        }
    }

    private function flashError(string $message): void
    {
        $_SESSION['flash_error'] = $message;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    private function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}
