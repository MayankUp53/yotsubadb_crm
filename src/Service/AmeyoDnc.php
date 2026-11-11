<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Http\Client;
use Cake\Log\Log;
use Throwable;

/** DNC request contract used by the original ContactsController::callDncAPI(). */
class AmeyoDnc
{
    public function __construct(private ?Client $client = null)
    {
    }

    public function call(string $phone, int $leadId, string $status): string
    {
        $mode = in_array($status, ['受注', '架電禁止（DNC）', '未確認（SV用）'], true) ? 'add' : 'del';
        $data = ['phone' => $phone, 'lead_id' => $leadId, 'mode' => $mode];
        try {
            // Preserve the legacy GET query shape: array($data), including its numeric outer key.
            $client = $this->client ?? new Client(['ssl_verify_peer' => false, 'timeout' => 30]);
            $response = $client->get('https://c056.ameyoj.com:8786/DNC_API/index.php', [$data]);
            $body = $response->getJson();
            if (!$response->isOk() || !is_array($body) || !isset($body['message']) || !is_string($body['message'])) {
                Log::error('Ameyo DNC returned an invalid response.');
                return '';
            }
            return $body['message'];
        } catch (Throwable $exception) {
            // A dialer outage must not prevent the CRM contact from being saved.
            Log::error('Ameyo DNC request failed.');
            return '';
        }
    }
}
