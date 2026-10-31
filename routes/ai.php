<?php

use App\Mcp\Servers\StoreServer;
use Laravel\Mcp\Facades\Mcp;

// MCP server for AI agents (streamable HTTP). Same bearer tokens, scopes and
// rate limit as the REST API: POST /api/v1/tokens, then
// `Authorization: Bearer <token>`. See docs/mcp.md.
Mcp::web('/mcp', StoreServer::class)->middleware(['auth:sanctum', 'throttle:api']);
