<?php
declare(strict_types=1);

class RateLimiter
{
    private string $storagePath;

    public function __construct(string $storagePath)
    {
        $this->storagePath = rtrim($storagePath, '/');
    }

    public function tooManyAttempts(string $key, int $limit, int $seconds): bool
    {
        $data = $this->read($key);
        $now = time();
        $attempts = array_filter($data['attempts'], static fn (int $timestamp) => $timestamp > ($now - $seconds));
        $data['attempts'] = $attempts;

        if (count($attempts) >= $limit) {
            $this->write($key, $data);
            return true;
        }

        $attempts[] = $now;
        $data['attempts'] = $attempts;
        $this->write($key, $data);
        return false;
    }

    private function read(string $key): array
    {
        $file = $this->storagePath . '/rate_' . md5($key) . '.json';
        if (!file_exists($file)) {
            return ['attempts' => []];
        }

        $raw = file_get_contents($file);
        $decoded = $raw ? json_decode($raw, true) : null;
        return is_array($decoded) ? $decoded : ['attempts' => []];
    }

    private function write(string $key, array $data): void
    {
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }

        $file = $this->storagePath . '/rate_' . md5($key) . '.json';
        file_put_contents($file, json_encode($data));
    }
}
