import "server-only";
import crypto from "node:crypto";
import type { SendResult, WhatsAppProvider } from "./types";

/**
 * Twilio WhatsApp Business API provider.
 *
 * Sends go to Twilio's REST API directly (form-encoded POST + HTTP
 * Basic auth) — no extra SDK dependency. Twilio prefixes WhatsApp
 * addresses with `whatsapp:`; callers pass plain E.164 numbers and
 * this adapter adds the prefix.
 *
 * Templates: Twilio sends pre-approved content via a Content SID
 * (`HX...`). `templateName` is expected to be a Twilio Content SID
 * when the org runs on the Twilio provider — a non-`HX` value throws
 * a clear error rather than silently sending the wrong thing.
 *
 * Webhook signature verification (adaptation note): Twilio's
 * `X-Twilio-Signature` algorithm is HMAC-SHA1 over the *exact
 * request URL Twilio was configured to call* plus the sorted POST
 * form params appended as `key+value` pairs (see
 * https://www.twilio.com/docs/usage/webhooks/webhooks-security). The
 * shared `WhatsAppProvider.verifyWebhookSignature(rawBody, headers)`
 * interface doesn't carry the request URL, so this provider
 * reconstructs it from `X-Forwarded-Proto` / `Host` headers plus the
 * fixed `TWILIO_WEBHOOK_PATH` below. The route handler that receives
 * Twilio's webhook (built by another agent) MUST be mounted at
 * exactly that path — or `TWILIO_WEBHOOK_PATH` must be updated to
 * match wherever it's mounted — otherwise every signature check will
 * fail closed.
 */

const TWILIO_API_BASE = "https://api.twilio.com/2010-04-01";

/** The path Twilio's inbound WhatsApp webhook must be mounted at. */
export const TWILIO_WEBHOOK_PATH = "/api/webhooks/twilio";

export interface TwilioCredentials {
  accountSid: string;
  authToken: string;
  whatsappNumber: string;
}

interface TwilioMessageResponse {
  sid?: string;
  message?: string;
}

function waAddress(number: string): string {
  const trimmed = number.trim();
  return trimmed.startsWith("whatsapp:") ? trimmed : `whatsapp:${trimmed}`;
}

function reconstructWebhookUrl(headers: Headers): string {
  const proto = headers.get("x-forwarded-proto") ?? "https";
  const host = headers.get("x-forwarded-host") ?? headers.get("host") ?? "";
  return `${proto}://${host}${TWILIO_WEBHOOK_PATH}`;
}

/** Parse an `application/x-www-form-urlencoded` raw body into a param map. */
function parseFormBody(rawBody: string): Record<string, string> {
  const params: Record<string, string> = {};
  for (const [key, value] of new URLSearchParams(rawBody)) {
    params[key] = value;
  }
  return params;
}

/** Build a `WhatsAppProvider` backed by the Twilio REST API. */
export function createTwilioProvider(creds: TwilioCredentials): WhatsAppProvider {
  const { accountSid, authToken, whatsappNumber } = creds;
  if (!accountSid || !authToken) {
    throw new Error("Twilio provider requires accountSid and authToken");
  }
  if (!whatsappNumber) {
    throw new Error("Twilio provider requires a whatsappNumber");
  }

  const authHeader =
    "Basic " + Buffer.from(`${accountSid}:${authToken}`).toString("base64");

  async function postMessage(params: Record<string, string>): Promise<SendResult> {
    params.From = waAddress(whatsappNumber);

    const response = await fetch(
      `${TWILIO_API_BASE}/Accounts/${accountSid}/Messages.json`,
      {
        method: "POST",
        headers: {
          Authorization: authHeader,
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams(params).toString(),
      },
    );

    const data = (await response
      .json()
      .catch(() => ({}))) as TwilioMessageResponse;
    if (!response.ok || !data.sid) {
      throw new Error(data.message || `Twilio API error: ${response.status}`);
    }
    return { messageId: data.sid };
  }

  return {
    name: "twilio",

    async sendText(to, body) {
      return postMessage({ To: waAddress(to), Body: body });
    },

    async sendTemplate(to, templateName, params) {
      if (!templateName.startsWith("HX")) {
        throw new Error(
          `Twilio requires a Content SID (HX...) to send a template; ` +
            `received "${templateName}". Map this template to a Twilio ` +
            `Content template and store its SID.`,
        );
      }

      const body: Record<string, string> = {
        To: waAddress(to),
        ContentSid: templateName,
      };

      if (params.length > 0) {
        const variables: Record<string, string> = {};
        params.forEach((value, index) => {
          variables[String(index + 1)] = String(value);
        });
        body.ContentVariables = JSON.stringify(variables);
      }

      return postMessage(body);
    },

    verifyWebhookSignature(rawBody, headers) {
      const signatureHeader = headers.get("x-twilio-signature");
      if (!signatureHeader) return false;

      const url = reconstructWebhookUrl(headers);
      const params = parseFormBody(rawBody);
      const sortedKeys = Object.keys(params).sort();

      let data = url;
      for (const key of sortedKeys) {
        data += key + params[key];
      }

      const expected = crypto
        .createHmac("sha1", authToken)
        .update(Buffer.from(data, "utf-8"))
        .digest("base64");

      const a = Buffer.from(signatureHeader);
      const b = Buffer.from(expected);
      if (a.length !== b.length) return false;
      return crypto.timingSafeEqual(a, b);
    },
  };
}
