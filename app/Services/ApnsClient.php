<?php

namespace App\Services;

use App\Models\DevicePushToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApnsClient
{
    public function enabled(): bool
    {
        return $this->keyId() !== ''
            && $this->teamId() !== ''
            && $this->bundleId() !== ''
            && $this->privateKey() !== '';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function send(DevicePushToken $device, string $title, string $body, int $badge, array $context = []): void
    {
        $imageUrl = trim((string) ($context['image_url'] ?? ''));
        $aps = [
            'alert' => [
                'title' => $title,
                'body' => $body,
            ],
            'sound' => 'default',
            'badge' => max(0, $badge),
            'category' => (string) ($context['category'] ?? 'WO_OPEN'),
        ];
        if ($imageUrl !== '') {
            $aps['mutable-content'] = 1;
        }

        $payload = ['aps' => $aps];
        foreach (['notification_id', 'type', 'product_id', 'order_id'] as $key) {
            if (isset($context[$key]) && $context[$key] !== '' && $context[$key] !== null) {
                $payload[$key] = $context[$key];
            }
        }
        if ($imageUrl !== '') {
            $payload['image_url'] = $imageUrl;
        }

        $this->deliver($device, $payload, [
            'apns-topic: '.$this->bundleId(),
            'apns-push-type: alert',
            'apns-priority: 10',
        ]);
    }

    public function sendSilent(DevicePushToken $device, int $badge): void
    {
        $this->deliver($device, [
            'aps' => [
                'content-available' => 1,
                'badge' => max(0, $badge),
            ],
        ], [
            'apns-topic: '.$this->bundleId(),
            'apns-push-type: background',
            'apns-priority: 5',
        ]);
    }

    /**
     * @param  array{projectName: string, orderId: int, status: string, detail: string}  $content
     */
    public function sendLiveActivity(DevicePushToken $device, string $event, array $content): void
    {
        $aps = [
            'timestamp' => time(),
            'event' => $event,
            'content-state' => [
                'status' => $content['status'],
                'detail' => $content['detail'],
            ],
        ];
        if ($event === 'start') {
            $aps['attributes-type'] = 'ProjectActivityAttributes';
            $aps['attributes'] = [
                'projectName' => $content['projectName'],
                'orderId' => $content['orderId'],
            ];
            $aps['alert'] = [
                'title' => $content['status'],
                'body' => $content['detail'],
            ];
        }
        if ($event === 'end') {
            $aps['dismissal-date'] = time();
        }

        $this->deliver($device, ['aps' => $aps], [
            'apns-topic: '.$this->bundleId().'.push-type.liveactivity',
            'apns-push-type: liveactivity',
            'apns-priority: 10',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $headers
     */
    private function deliver(DevicePushToken $device, array $payload, array $headers): void
    {
        if (! $this->enabled() || ! function_exists('curl_init')) {
            return;
        }

        $host = $device->environment === 'sandbox'
            ? 'https://api.sandbox.push.apple.com'
            : 'https://api.push.apple.com';
        $ch = curl_init($host.'/3/device/'.$device->token);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2TLS,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => array_merge([
                'authorization: bearer '.$this->jwt(),
                'content-type: application/json',
            ], $headers),
        ]);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($status === 200) {
            return;
        }

        $reason = '';
        if (is_string($response) && $response !== '') {
            $decoded = json_decode($response, true);
            $reason = is_array($decoded) ? (string) ($decoded['reason'] ?? '') : '';
        }

        if ($status === 410 || $reason === 'BadDeviceToken' || $reason === 'Unregistered') {
            $device->delete();
        }

        Log::warning('apns_send_failed', [
            'status' => $status,
            'reason' => $reason !== '' ? $reason : $error,
            'environment' => $device->environment,
        ]);
    }

    private function jwt(): string
    {
        return Cache::remember('penjualan.apns.jwt', 2400, function (): string {
            $header = $this->base64Url(json_encode(['alg' => 'ES256', 'kid' => $this->keyId()]));
            $claims = $this->base64Url(json_encode(['iss' => $this->teamId(), 'iat' => time()]));
            $data = $header.'.'.$claims;
            $key = openssl_pkey_get_private($this->privateKey());
            if ($key === false) {
                throw new \RuntimeException('APNs private key tidak valid.');
            }
            $signature = '';
            if (! openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA256)) {
                throw new \RuntimeException('APNs JWT tidak dapat ditandatangani.');
            }

            return $data.'.'.$this->base64Url($this->derToRaw($signature));
        });
    }

    private function derToRaw(string $der): string
    {
        $offset = 0;
        if (ord($der[$offset]) !== 0x30) {
            return $der;
        }
        $offset++;
        $this->readLength($der, $offset);
        if (ord($der[$offset]) !== 0x02) {
            return $der;
        }
        $offset++;
        $rLength = $this->readLength($der, $offset);
        $r = substr($der, $offset, $rLength);
        $offset += $rLength;
        if (ord($der[$offset]) !== 0x02) {
            return $der;
        }
        $offset++;
        $sLength = $this->readLength($der, $offset);
        $s = substr($der, $offset, $sLength);
        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");

        return str_pad($r, 32, "\x00", STR_PAD_LEFT).str_pad($s, 32, "\x00", STR_PAD_LEFT);
    }

    private function readLength(string $der, int &$offset): int
    {
        $length = ord($der[$offset]);
        $offset++;
        if (($length & 0x80) === 0) {
            return $length;
        }
        $count = $length & 0x7f;
        $length = 0;
        for ($i = 0; $i < $count; $i++) {
            $length = ($length << 8) | ord($der[$offset]);
            $offset++;
        }

        return $length;
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function keyId(): string
    {
        return trim((string) config('services.apns.key_id'));
    }

    private function teamId(): string
    {
        return trim((string) config('services.apns.team_id'));
    }

    private function bundleId(): string
    {
        return trim((string) config('services.apns.bundle_id'));
    }

    private function privateKey(): string
    {
        $inline = trim((string) config('services.apns.private_key'));
        if ($inline !== '') {
            return str_replace('\n', "\n", $inline);
        }

        $path = trim((string) config('services.apns.private_key_path'));
        if ($path === '') {
            return '';
        }
        if (! str_starts_with($path, '/')) {
            $path = base_path($path);
        }
        if (! is_file($path)) {
            return '';
        }

        return (string) file_get_contents($path);
    }
}
