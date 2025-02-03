<?php

namespace App\Console\Commands;

use App\WebSockets\Socket;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Illuminate\Console\Command;
use Ratchet\WebSocket\WsServer;

class WebSocketServer extends Command
{
    protected $signature = 'websocket:serve';
    protected $description = 'Start the WebSocket server';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $this->info('Starting WebSocket server...');
        $server = IoServer::factory(new HttpServer(new WsServer(new Socket())), 8088);
        $server->run();
    }
}