import "server-only";
import crypto from "node:crypto";
import type { SendResult, WhatsAppProvider } from "./types";

/**
 * Meta WhatsApp Cloud API provider.
 *
 * Sends go straight to `https://graph.facebook.com/{version}/{phoneNumberId}/messages`
 * with a bearer token; webhook verification validates the
 * `X-Hub-Signature-256` header Meta attaches to every inbound POST,
 * HMAC-SHA256'd over the raw body using the org's own Meta app
 * secret (see `StoredWhatsAppCredentials`'s `appSecret` field — each
 * BYO org has its own Meta app, so there is no single shared
 * platform secret to verify against).
 */

const META_API_VERSION = "v20.0";
const META_API_BASE = `https://graph.facebook.com/${META_API_VERSION}`;

export interface MetaCredentials {
  phoneNumberId: string;
  wabaId: string;
  accessToken: string;
  appSecret: string;
}

interface MetaErrorResponse {
  error?: { message?: string; code?: number; type?: string };
}

interface MetaSendResponse {
  messages?: { id: string }[];
}

async function metaRequest(
  phoneNumberId: string,
  accessToken: string,
  body: Record<string, unknown>,
): Promise<SendResult> {
  const response = await fetch(`${META_API_BASE}/${phoneNumberId}/messages`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${accessToken}`,
    },
    body: JSON.stringify({
      messaging_product: "whatsapp",
      recipient_type: "individual",
      ...body,
    }),
  });

  const data = (await response.json().catch(() => ({}))) as
    | MetaSendResponse
    | MetaErrorResponse;

  if (!response.ok) {
    const message =
      (data as MetaErrorResponse).error?.message ||
      `Meta API error: ${response.status}`;
    throw new Error(message);
  }

  const messageId = (data as MetaSendResponse).messages?.[0]?.id;
  if (!messageId) {
    throw new Error("Meta API did not return a message id.");
  }
  return { messageId };
}

/** Build a `WhatsAppProvider` backed by the Meta Cloud API. */
export function createMetaProvider(creds: MetaCredentials): WhatsAppProvider {
  const { phoneNumberId, accessToken, appSecret } = creds;
  if (!phoneNumberId || !accessToken) {
    throw new Error("Meta provider requires phoneNumberId and accessToken");
  }

  return {
    name: "meta",

    async sendText(to, body) {
      return metaRequest(phoneNumberId, accessToken, {
        to,
        type: "text",
        text: { body },
      });
    },

    async sendTemplate(to, templateName, params) {
      const template: Record<string, unknown> = {
        name: templateName,
        language: { code: "en_US" },
      };
      if (params.length > 0) {
        template.components = [
          {
            type: "body",
            parameters: params.map((p) => ({ type: "text", text: String(p) })),
          },
        ];
      }
      return metaRequest(phoneNumberId, accessToken, {
        to,
        type: "template",
        template,
      });
    },

    verifyWebhookSignature(rawBody, headers) {
      if (!appSecret) return false;
      const signatureHeader = headers.get("x-hub-signature-256");
      if (!signatureHeader || !signatureHeader.startsWith("sha256=")) return false;

      const expected =
        "sha256=" +
        crypto.createHmac("sha256", appSecret).update(rawBody).digest("hex");

      const a = Buffer.from(signatureHeader);
      const b = Buffer.from(expected);
      if (a.length !== b.length) return false;
      return crypto.timingSafeEqual(a, b);
    },
  };
}
