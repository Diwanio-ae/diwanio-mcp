# Diwanio MCP authentication

Diwanio MCP exposes customer-specific financial data and draft actions, so the
MCP server requires an authenticated OAuth connection before it serves the
transport or executes a tool. The portable package does not collect, store, or
exchange credentials.

## Connection flow

1. The host connects to https://diwanio.ae/mcp/diwanio.
2. The server returns 401 Unauthorized with a WWW-Authenticate challenge that
   points to the protected-resource metadata:
   https://diwanio.ae/.well-known/oauth-protected-resource/mcp/diwanio.
3. The host reads that metadata and discovers the authorization server.
4. The host reads the authorization-server metadata and starts an OAuth 2.1
   authorization-code flow with PKCE (S256) and the mcp:use scope.
5. The user signs in and grants access. The host sends the resulting bearer
   token as Authorization: Bearer TOKEN.
6. The server validates the token, user, tenant, subscription entitlement, and
   required scope before serving MCP requests.

The user should complete this flow in the MCP host. Do not paste an access
token, client secret, Passport key, or other credential into the skill, plugin
repository, chat, or tool arguments.

## Discovery endpoints

Once the Laravel changes are deployed, the server publishes endpoint-specific
metadata:

- Protected resource:
  https://diwanio.ae/.well-known/oauth-protected-resource/mcp/diwanio
- Authorization server:
  https://diwanio.ae/.well-known/oauth-authorization-server/mcp/diwanio
- MCP resource:
  https://diwanio.ae/mcp/diwanio

The Diwanio Laravel server owns the OAuth endpoints under /oauth, including
authorization, token exchange, and dynamic client registration. The exact
authorization-server metadata is authoritative for the host.

## Package/server boundary

The package's mcp.json contains only the remote MCP URL. There is intentionally
no portable auth field in this manifest: authentication is part of the MCP
server integration and the host's OAuth flow.

The server integration is responsible for:

- returning the protected-resource 401 challenge;
- publishing protected-resource and authorization-server metadata;
- enforcing OAuth 2.1 authorization-code + PKCE;
- advertising and enforcing the mcp:use scope;
- validating the bearer token and resolving the authenticated tenant;
- applying Diwanio subscription gates before tool execution; and
- returning the authenticated profile tool with _meta["openai/profile"] = true
  when the host supports connected-account linking.

The repository must never contain OAuth client secrets, access/refresh tokens,
Passport encryption keys, or production key material.

## Server-side validation

The Laravel server's focused MCP coverage validates the discovery documents,
WWW-Authenticate challenge, scope enforcement, tenant/subscription gates,
per-tool OAuth security schemes, and profile linking contract:

~~~
php -d memory_limit=1G artisan test --compact tests/Feature/Mcp/DiwanioMcpAccessTest.php
~~~

Passport keys must be provisioned in the target environment's secret store with
php artisan passport:keys (or the deployment's equivalent). Do not commit
generated keys. After deployment, verify the production discovery URLs and
complete a real host OAuth smoke test before claiming the connection is
available.
