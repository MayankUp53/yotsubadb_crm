<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Service\AmeyoDnc;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** No requests leave the test process. */
class AmeyoDncTest extends TestCase
{
    public function testLegacyStatusModesAndGetContract(): void
    {
        foreach (['受注' => 'add', '架電禁止（DNC）' => 'add', '未確認（SV用）' => 'add', '見込み' => 'del', '' => 'del'] as $status => $mode) {
            $client = $this->createMock(Client::class);
            $client->expects(self::once())->method('get')
                ->with('https://c056.ameyoj.com:8786/DNC_API/index.php', [['phone' => '0312345678', 'lead_id' => 15, 'mode' => $mode]])
                ->willReturn(new Response(['HTTP/1.1 200 OK', 'Content-Type: application/json'], '{"result":"ok","message":"DNC updated"}'));
            self::assertSame('DNC updated', (new AmeyoDnc($client))->call('0312345678', 15, $status));
        }
    }

    public function testInvalidResponsesAndOutageReturnEmptyMessage(): void
    {
        foreach ([new Response(['HTTP/1.1 500 Server Error'], '{}'), new Response(['HTTP/1.1 200 OK'], 'not-json'), new Response(['HTTP/1.1 200 OK'], '{"message":[]}')] as $response) {
            $client = $this->createMock(Client::class);
            $client->expects(self::once())->method('get')->willReturn($response);
            self::assertSame('', (new AmeyoDnc($client))->call('0312345678', 15, '受注'));
        }
        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('get')->willThrowException(new RuntimeException('Connection unavailable'));
        self::assertSame('', (new AmeyoDnc($client))->call('0312345678', 15, '受注'));
    }
}
