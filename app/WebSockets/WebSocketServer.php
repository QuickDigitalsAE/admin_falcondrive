<?php

namespace App\WebSockets;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Psr\Http\Message\RequestInterface;
use App\Models\UserSocketConnection;
use Illuminate\Support\Carbon;

class WebSocketServer implements MessageComponentInterface
{
    protected $clients;
    protected $clientMap;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->clientMap = [];

        echo "✅ WebSocket server started...\n";
    }


    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $queryParams);

        $userId = $queryParams['user_id'] ?? null;

        if ($userId) {
            $conn->user_id = $userId;
            $conn->connection_id = uniqid('ws_', true);

            // Save to DB
            UserSocketConnection::create([
                'user_id' => $userId,
                'connection_id' => $conn->connection_id,
                'connected_at' => now(),
                'disconnected_at' => null,
            ]);

            // Map connection ID to client
            $this->clientMap[$conn->connection_id] = $conn;

            echo "Connected: user_id = {$userId}, conn_id = {$conn->connection_id}\n";
        }
    }


    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        $toUserId = $data['to_user_id'] ?? null;
        $message = $data['message'] ?? '';

        if ($toUserId) {
            $records = UserSocketConnection::where('user_id', $toUserId)
                        ->whereNull('disconnected_at')
                        ->pluck('connection_id');

            foreach ($records as $connId) {
                if (isset($this->clientMap[$connId])) {
                    $this->clientMap[$connId]->send(json_encode([
                        'type' => 'notification',
                        'from_user_id' => $from->user_id ?? null,
                        'message' => $message,
                    ]));

                    echo "Message sent to user_id = $toUserId, connection_id = $connId\n";
                }
            }
        }
    }


    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        if (isset($conn->connection_id)) {
            unset($this->clientMap[$conn->connection_id]);

            UserSocketConnection::where('connection_id', $conn->connection_id)->delete();

            echo "Disconnected: conn_id = {$conn->connection_id}, user_id = {$conn->user_id}\n";
        }
    }


    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}
