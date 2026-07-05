import "server-only";

/**
 * Provider-agnostic WhatsApp layer.
 *
 * Callers (campaign sending, inbox replies, webhook routes) talk to
 * exactly one interface — `WhatsAppProvider` — and never branch on
 * the provider name. The concrete provider (Meta Cloud API or
 * Twilio) is chosen per org from its `whatsapp_connections.provider`
 * column via `loadProviderForOrg()` (see `load-provider.ts`).
 */

/** Result of a successful outbound send. */
export interface SendResult {
  messageId: string;
}

export interface WhatsAppProvider {
  readonly name: "meta" | "twilio";

  /**
   * Free-form text. Only deliverable inside the 24-hour customer
   * service window opened by an inbound message from the contact.
   */
  sendText(to: string, body: string): Promise<SendResult>;

  /**
   * Pre-approved template send — required for business-initiated
   * conversations outside the 24h window (both Meta and Twilio
   * require templates to be pre-approved by WhatsApp). `params` are
   * positional body variables: Meta fills them into `{{1}}`, `{{2}}`,
   * ...; Twilio fills them into Content template variables `"1"`,
   * `"2"`, ...
   */
  sendTemplate(
    to: string,
    templateName: string,
    params: string[],
  ): Promise<SendResult>;

  /**
   * Validate an inbound webhook request against this connection's
   * stored secret. Fails closed (returns `false`, never throws) if
   * the secret is missing, malformed, or the signature doesn't
   * match — callers should treat `false` as "reject the request".
   */
  verifyWebhookSignature(rawBody: string, headers: Headers): boolean;
}

/**
 * Decrypted shape of `whatsapp_connections.credentials_encrypted`
 * (this is `JSON.parse(decrypt(row.credentials_encrypted))`). The
 * `provider` discriminant matches the DB column of the same name.
 * `personal` connections carry no credentials at all and are never
 * represented here — see `loadProviderForOrg()`.
 */
export type StoredWhatsAppCredentials =
  | {
      provider: "meta";
      phoneNumberId: string;
      wabaId: string;
      accessToken: string;
      /**
       * The org's own Meta app secret. Each BYO org runs its own
       * Meta app (to get their own Cloud API credentials), so
       * webhook signatures must be verified per-org against that
       * org's app secret rather than one shared platform secret.
       */
      appSecret: string;
    }
  | {
      provider: "twilio";
      accountSid: string;
      authToken: string;
      /** E.164 WhatsApp sender, e.g. "+14155238886". */
      whatsappNumber: string;
    };
