<?php

use App\Mcp\Servers\LogWatcherServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/log-watcher', LogWatcherServer::class);
