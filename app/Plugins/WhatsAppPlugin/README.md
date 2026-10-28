# WhatsApp Plugin

Sends WhatsApp messages and sale documents to customers through the [WhatsApp Business Cloud API](https://developers.facebook.com/documentation/business-messaging/whatsapp/get-started), and records the replies.

The plugin is self-contained: it adds no file to core, and everything it installs (its settings, its module entry and its `whatsapp_messages` table) is removed again on uninstall.

## What it adds

- A **WhatsApp** office module — a page listing recent conversations, with a send form and the full thread per customer.
- A **Send via WhatsApp** button on all four sale documents (invoice, quote, work order, receipt), which delivers the document as a PDF with the configured invoice message as its caption.
- A per-person send form at `whatsapp/view/{person_id}`, mirroring the SMS Messages one: prefilled with that person's number, their saved message and their thread.
- A webhook that stores inbound replies and delivery-status updates (`sent` → `delivered` → `read`, or `failed`).

## Requirements

A Meta developer app with WhatsApp Business enabled. From it you need:

| Setting | Where it comes from | Required |
| --- | --- | --- |
| Phone Number ID | WhatsApp → API Setup | Yes |
| Access Token | WhatsApp → API Setup (use a permanent System User token; the test token expires in 24 hours) | Yes |
| WhatsApp Business Account ID | WhatsApp → API Setup | No |
| App Secret | App Settings → Basic | Only to receive replies |
| Verify Token | Any string you choose; you enter the same one in Meta | Only to receive replies |
| Default Country Code | Your locale, digits only (e.g. `1`) | No |

The site must be reachable over public HTTPS to receive replies. Sending works without that.

## Installation

1. **Plugins → WhatsApp → Install.** This creates the settings, registers the `whatsapp` module and permission, and runs the migration that creates `whatsapp_messages`.
2. **Grant the `whatsapp` permission** to the employees who should see the module (Employees → edit → permissions).
3. **Configure** (the gear icon on the plugin row) and enter at least the Phone Number ID and Access Token.
4. **Enable** the plugin.

The Access Token and App Secret are stored encrypted, and are never rendered back into the configuration form: leave a secret field blank to keep the stored value, type a new one to replace it, or tick its **Clear the stored value** box to remove it.

## Receiving replies

1. Copy the **Callback URL** shown in the configuration form (`https://your-site/plugins/whatsapp/webhook`).
2. In the Meta app dashboard, WhatsApp → Configuration → Webhooks, paste it as the callback URL along with the same **Verify Token** you saved here, and subscribe to the `messages` field.
3. Save the **App Secret** here. Inbound payloads whose `X-Hub-Signature-256` does not match it are discarded, and with no app secret stored nothing inbound is accepted at all.

## Sending

Free-form messages are only delivered inside WhatsApp's 24-hour customer service window — that is, within 24 hours of the customer's last message to you. Outside it Meta requires an approved message template, which this plugin does not send. A message rejected for that reason is logged with the API's error against the conversation.

Numbers are normalized to the digits-only international form the API expects. A number entered with a leading `+` is taken as already international; otherwise the Default Country Code is prepended when the number does not already start with it.

## Uninstalling

Uninstall removes the module, the permission and the settings, and **drops `whatsapp_messages`** — the conversation history goes with it. Disable the plugin instead if you only want to stop sending.
