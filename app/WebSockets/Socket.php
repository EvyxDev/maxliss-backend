<?php

namespace App\WebSockets;

use App\Models\Expert;
use App\Models\User;
use App\Models\NewMessage;
use Ratchet\ConnectionInterface;
use Illuminate\Support\Facades\Log;
use Ratchet\MessageComponentInterface;

class Socket implements MessageComponentInterface
{
    protected $clients;
    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $queryArray);
        $wss_token = $queryArray['wss_token'];
        $user_type = $queryArray['user_type'];
        if ($user_type == "user") {
            $user = User::where('wss_token', $wss_token)->first();
        } else {
            $user = Expert::where('wss_token', $wss_token)->first();
        }

        if (!isset($queryArray['wss_token'])  || !isset($queryArray['user_type']) ||  !$user) {
            $conn->close();
            return;
        }
        $conn->resourceId = $wss_token;
        $this->clients->attach($conn, [
            'wss_token' => $wss_token,
        ]);
    }
    public function onMessage(ConnectionInterface $from, $msg)
    {
        try {
            $msg = preg_replace('/,\s*}/', '}', $msg);
            $msg = preg_replace('/,\s*]/', ']', $msg);
            $data = json_decode($msg, true);
            $queryString = $from->httpRequest->getUri()->getQuery();
            parse_str($queryString, $queryArray);
            $wss_token = $queryArray['wss_token'];
            $user = User::where('wss_token', $wss_token)->first();
            if ($queryArray['user_type'] == "user") {
                $sender_id = $user->id;
                $receiver_id = User::where('user_type', 'admin')->first()->id;
            } else {
                $sender_id = User::where('user_type', 'admin')->first()->id;
                $receiver_id = $user->id;
            }
            $message = new NewMessage();
            $message->sender_id = $user->id;
            $message->receiver_id = User::where('user_type', 'admin')->first()->id;
            $message->message = $data['msg'];
            $message->save();
            $replyMessage = new NewMessage();
            $replyMessage->sender_id = User::where('user_type', 'admin')->first()->id;
            $replyMessage->receiver_id = $user->id;
            $replyMessage->message = "Automated Message";
            $replyMessage->save();
            foreach ($this->clients as $client) {
                if ($client !== $from) {
                    $client->send(json_encode([
                        'msg' => $message->message,
                    ]));
                }
                if ($client->resourceId === $wss_token) {
                    $client->send(json_encode([
                        'msg' => $replyMessage->message,
                    ]));
                }
            }
        } catch (\Exception $e) {
            Log::error('Message saving failed: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
    }
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        Log::error('Message saving failed: ' . $e->getMessage());
        dd($e);
        $conn->close();
    }
}
