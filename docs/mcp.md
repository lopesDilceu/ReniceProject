# MCP server

The store exposes a [Model Context Protocol](https://modelcontextprotocol.io) server at **`/mcp`** (streamable HTTP), built with [`laravel/mcp`](https://laravel.com/docs/mcp). An AI assistant can search the catalog, manage a customer's cart and prepare an order. The customer still confirms every order in the store.

## Connecting a client

The server uses the same tokens as the [REST API](api/README.md):

```bash
curl -X POST http://localhost/api/v1/tokens \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"email": "cliente@renice.test", "password": "password", "device_name": "Claude Code"}'
```

**Claude Code:**

```bash
claude mcp add --transport http renice http://localhost/mcp \
  --header "Authorization: Bearer <token>"
```

Then ask, for example: *"Find an anime cup with ice, put two in my cart and prepare the order for my saved address."*

**Any other client:** use the streamable HTTP transport, the URL `http://<host>/mcp` and the header `Authorization: Bearer <token>`. The [MCP Inspector](https://modelcontextprotocol.io/docs/tools/inspector) works for trying tools by hand.

## Tools

| Tool | Scope | |
|---|---|---|
| `search-products` | read | Search the catalog: query, sort, available only, page |
| `get-product` | read | One product, and whether a quantity can be ordered (`max_quantity`; exact stock is not disclosed) |
| `view-cart` | read | The customer's cart |
| `list-addresses` | read | Saved addresses, for `start-checkout` |
| `list-orders` | read | Orders, or one order in full, including the Pix code while it is unpaid |
| `add-to-cart` | write | Add a product, checked against stock |
| `set-cart-quantity` | write | Change a quantity; 0 removes the item |
| `start-checkout` | write | Prepare the order and return the confirmation link |

- **Scopes:** a token with only the `read` scope does not see the write tools at all; they are neither listed nor callable.
- **Annotations:** read tools are marked `readOnlyHint`, and `set-cart-quantity` is `idempotentHint`, so clients can skip asking before using them.
- **Isolation:** every tool acts only on the token owner's cart, addresses and orders. Another customer's order is "not found".

## Why the agent cannot place orders

`start-checkout` does not place anything. It returns a summary and a **signed link** to `/checkout/confirmar`, valid for 15 minutes. The customer opens the link signed in to their account, reviews the order and confirms it.

The link pins the customer, the address and the exact cart: products, quantities and prices.

| If… | The link… |
|---|---|
| the cart or a price changed after the agent prepared it | sends the customer to the normal checkout, and nothing is ordered |
| another account opens it | answers 403 |
| it was edited, or is more than 15 minutes old | answers 403 (signature) |
| the order was already placed (the cart is now empty) | places nothing more |

So even an agent misled by prompt injection, such as instructions hidden in a web page, can at most fill a cart. Spending money always takes a person's click, in the store and not in the chat.

## Testing

| Test file | What it covers |
|---|---|
| `tests/Feature/Mcp/StoreServerTest.php` | each tool, scopes and isolation |
| `AgentCheckoutTest.php` | the confirmation link: placing the order once, a changed cart or price, someone else's account, tampering, expiry |
| `McpHttpTest.php` | the real transport: 401 without a token, tool listing by scope, a tool call |
