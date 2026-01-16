<?php
declare(strict_types=1);

class PushService
{
    public function sendToUser(int $userId, array $payload): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT endpoint, p256dh, auth FROM push_subscriptions WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $subscriptions = $stmt->fetchAll();

        $sent = 0;
        foreach ($subscriptions as $subscription) {
            $result = $this->send($subscription, $payload);
            if ($result['status'] === 'gone') {
                $this->invalidate($subscription['endpoint']);
                continue;
            }
            if ($result['status'] === 'ok') {
                $sent++;
            }
        }

        return $sent;
    }

    public function send(array $subscription, array $payload): array
    {
        if (VAPID_PUBLIC_KEY === '' || VAPID_PRIVATE_KEY === '') {
            return ['status' => 'error', 'message' => 'VAPID ключи не настроены'];
        }

        $endpoint = $subscription['endpoint'] ?? '';
        if ($endpoint === '') {
            return ['status' => 'error', 'message' => 'endpoint пустой'];
        }

        $userPublicKey = $this->base64UrlDecode($subscription['p256dh'] ?? '');
        $userAuth = $this->base64UrlDecode($subscription['auth'] ?? '');
        if ($userPublicKey === '' || $userAuth === '') {
            return ['status' => 'error', 'message' => 'ключи подписки отсутствуют'];
        }

        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $encrypted = $this->encryptPayload($payloadJson, $userPublicKey, $userAuth);

        $jwt = $this->createVapidJwt($endpoint);
        $headers = [
            'TTL: 300',
            'Content-Encoding: aes128gcm',
            'Content-Type: application/octet-stream',
            'Authorization: WebPush ' . $jwt,
            'Crypto-Key: dh=' . $encrypted['public_key'] . '; p256ecdsa=' . VAPID_PUBLIC_KEY,
            'Encryption: salt=' . $encrypted['salt'],
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $encrypted['payload'],
        ]);
        curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (in_array($statusCode, [404, 410], true)) {
            return ['status' => 'gone'];
        }
        if (in_array($statusCode, [200, 201, 202], true)) {
            return ['status' => 'ok'];
        }
        return ['status' => 'error', 'code' => $statusCode];
    }

    public function invalidate(string $endpoint): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM push_subscriptions WHERE endpoint = :endpoint');
        $stmt->execute(['endpoint' => $endpoint]);
    }

    private function createVapidJwt(string $endpoint): string
    {
        $url = parse_url($endpoint);
        $audience = ($url['scheme'] ?? 'https') . '://' . ($url['host'] ?? '');
        if (!empty($url['port'])) {
            $audience .= ':' . $url['port'];
        }

        $token = [
            'aud' => $audience,
            'exp' => time() + 60 * 60 * 12,
            'sub' => VAPID_SUBJECT !== '' ? VAPID_SUBJECT : 'mailto:admin@example.com',
        ];

        $header = [
            'typ' => 'JWT',
            'alg' => 'ES256',
        ];

        $encodedHeader = $this->base64UrlEncode(json_encode($header));
        $encodedPayload = $this->base64UrlEncode(json_encode($token));
        $signingInput = $encodedHeader . '.' . $encodedPayload;

        $privateKey = $this->createPemPrivateKey();
        $signature = '';
        openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signature = $this->derToJose($signature, 64);

        return $signingInput . '.' . $this->base64UrlEncode($signature);
    }

    private function encryptPayload(string $payload, string $userPublicKey, string $userAuth): array
    {
        $salt = random_bytes(16);
        $localKey = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        $details = openssl_pkey_get_details($localKey);
        $localPublicKey = "\x04" . $details['ec']['x'] . $details['ec']['y'];
        $localPublicKeyPem = $details['key'];

        $userPublicPem = $this->createPemPublicKey($userPublicKey);
        $userPublicResource = openssl_pkey_get_public($userPublicPem);

        $sharedSecret = openssl_pkey_derive($userPublicResource, $localKey, 32);
        $prk = $this->hkdf($userAuth, $sharedSecret, "Content-Encoding: auth\0", 32);

        $context = "P-256\0"
            . pack('n', strlen($userPublicKey)) . $userPublicKey
            . pack('n', strlen($localPublicKey)) . $localPublicKey;

        $cek = $this->hkdf($salt, $prk, "Content-Encoding: aes128gcm\0" . $context, 16);
        $nonce = $this->hkdf($salt, $prk, "Content-Encoding: nonce\0" . $context, 12);

        $record = "\0\0" . $payload;
        $tag = '';
        $ciphertext = openssl_encrypt($record, 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag);

        return [
            'payload' => $ciphertext . $tag,
            'salt' => $this->base64UrlEncode($salt),
            'public_key' => $this->base64UrlEncode($localPublicKey),
            'public_key_pem' => $localPublicKeyPem,
        ];
    }

    private function hkdf(string $salt, string $ikm, string $info, int $length): string
    {
        $prk = hash_hmac('sha256', $ikm, $salt, true);
        $output = '';
        $block = '';
        $counter = 1;
        while (strlen($output) < $length) {
            $block = hash_hmac('sha256', $block . $info . chr($counter), $prk, true);
            $output .= $block;
            $counter++;
        }
        return substr($output, 0, $length);
    }

    private function createPemPublicKey(string $raw): string
    {
        $der = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200') . $raw;
        return $this->pemEncode($der, 'PUBLIC KEY');
    }

    private function createPemPrivateKey(): string
    {
        $privateRaw = $this->base64UrlDecode(VAPID_PRIVATE_KEY);
        $publicRaw = $this->base64UrlDecode(VAPID_PUBLIC_KEY);
        $der = hex2bin('30770201010420')
            . $privateRaw
            . hex2bin('a00a06082a8648ce3d030107a144034200')
            . $publicRaw;
        return $this->pemEncode($der, 'EC PRIVATE KEY');
    }

    private function pemEncode(string $der, string $type): string
    {
        $encoded = chunk_split(base64_encode($der), 64, "\n");
        return "-----BEGIN {$type}-----\n{$encoded}-----END {$type}-----\n";
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        if ($data === '') {
            return '';
        }
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }

    private function derToJose(string $der, int $length): string
    {
        $pos = 0;
        if (ord($der[$pos]) !== 0x30) {
            return str_repeat("\0", $length);
        }
        $pos++;
        $seqLength = ord($der[$pos++]);
        if ($seqLength & 0x80) {
            $lengthBytes = $seqLength & 0x0f;
            $seqLength = 0;
            for ($i = 0; $i < $lengthBytes; $i++) {
                $seqLength = ($seqLength << 8) | ord($der[$pos++]);
            }
        }

        if (ord($der[$pos++]) !== 0x02) {
            return str_repeat("\0", $length);
        }
        $rLength = ord($der[$pos++]);
        $r = substr($der, $pos, $rLength);
        $pos += $rLength;
        if (ord($der[$pos++]) !== 0x02) {
            return str_repeat("\0", $length);
        }
        $sLength = ord($der[$pos++]);
        $s = substr($der, $pos, $sLength);

        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");
        $r = str_pad($r, $length / 2, "\x00", STR_PAD_LEFT);
        $s = str_pad($s, $length / 2, "\x00", STR_PAD_LEFT);
        return $r . $s;
    }
}
