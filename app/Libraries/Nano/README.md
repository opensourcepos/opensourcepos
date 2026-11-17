# Nano (XNO) payments

This library lets the register take **Nano (XNO)** at the counter, settling the
exact amount of a sale directly to an account the operator owns. It is **off by
default** and changes nothing in OSPOS until an operator turns it on; when off,
no payment option, validation path or behaviour is added.

Nano confirms in under a second and has no fee, so a cashier can take a chain
payment at the counter without a processor. There is **no key and no seed**
anywhere in OSPOS: the account is yours, the payer holds their own wallet, and
OSPOS only *reads* public ledger data from a node you name.

## Turning it on

Set these OSPOS settings (keys in `app_config`; defaults in
`app/Config/OSPOS.php`):

| Setting | Meaning |
| --- | --- |
| `nano_enabled` | `1` to offer Nano at the register. |
| `nano_account` | your receiving account, a checksum-valid `nano_...` address. |
| `nano_node_url` | an `http(s)` Nano RPC node URL you control or trust (read only). |
| `nano_xno_price` | XNO per one unit of your sale currency (e.g. `3.2` means 1.00 = 3.2 XNO). |
| `nano_replay_window_seconds` | how recent a send block must be to be accepted (default `86400`). |

## How a sale is paid

1. The cashier selects **Nano (XNO)** as the payment type.
2. The register calls `GET sales/nanoPaymentRequest`, which returns the exact
   amount to send — in XNO and in **raw** (1 XNO = 10^30 raw), derived from the
   sale total and your price with no floating-point rounding — and the
   **representative account** the payer must set on their send block. That
   representative is a commitment to *this* sale
   (`SHA256("xno-binding/v1/rep|" + orderRef + "|" + account + "|" + priceRaw)`
   encoded as a `nano_` account — option C of the
   [xno-binding v1 standard](https://github.com/dhyabi2/xno-call-binding/blob/main/docs/BINDING-STANDARD.md)),
   so a given block belongs to exactly one sale at exactly one price.
3. The customer sends, and the cashier enters the resulting **block hash** as the
   payment's reference code.
4. OSPOS reads that block from your node (`block_info`) and credits the sale only
   when the block is a **confirmed send** of the **exact raw amount**, to **your
   account**, carrying **this sale's representative commitment**, and recent
   enough. A durable, fsynced **claims ledger** records the block so one block
   credits one sale once — and the record survives a restart, so an old public
   block can never be replayed.

The payment is recorded in your own sale currency, exactly like any other
non-cash payment; the Nano block hash is its reference. The amount credited is
the sale amount the quote was made for, kept on the server, never the amount
typed in the register; if the sale changes after the quote, the payment is
refused until Nano is selected again. Nano is offered only in the register
(not in sale edit, receivings or expenses), and an outstanding quote is kept
with a suspended sale and restored when it is unsuspended.

## Design

Every decision lives in small, dependency-free classes that unit-test on their
own, with the node read and the clock injected (`tests/Libraries/NanoPaymentTest.php`):

- `Blake2b` — RFC 7693 BLAKE2b at the 5-byte digest a Nano checksum needs.
- `Address` — `nano_`/`xrb_` codec; the checksum is always verified.
- `Amount` — raw arithmetic as integer strings; no amount is rounded through a float.
- `Binding` — the xno-binding v1 option C commitment.
- `Verifier` — accepts/refuses a `block_info` reply; no key, no network of its own.
- `ClaimsFile` — the restart-proof, single-lock claim ledger.
- `PaymentGateway` — the thin register adapter that ties them together.
