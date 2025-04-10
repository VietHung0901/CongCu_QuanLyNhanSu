<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WebSocketServer implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "--->Client mới kết nối: ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo "Nhận dữ liệu từ client ({$from->resourceId}): $msg\n";

        // Gửi HTML trực tiếp cho tất cả client đang kết nối
        foreach ($this->clients as $client) {
            $client->send($msg);
        }

        echo "Đã gửi HTML check-in đến tất cả client\n";
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Client ({$conn->resourceId}) đã ngắt kết nối\n";
    }

    public function onError(ConnectionInterface $conn, \Throwable $e)
    {
        echo "Lỗi: " . $e->getMessage() . "\n";
        $conn->close();
    }
}

// Khởi chạy server WebSocket
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WebSocketServer()
        )
    ),
    8080
);

echo "WebSocket Server chạy trên cổng 8080...\n";
$server->run();
