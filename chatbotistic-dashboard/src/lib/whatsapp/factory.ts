import "server-only";
import { createMetaProvider } from "./meta-provider";
import { createTwilioProvider } from "./twilio-provider";
import type { StoredWhatsAppCredentials, WhatsAppProvider } from "./types";

/**
 * Build a live `WhatsAppProvider` from decrypted, provider-specific
 * credentials. Routes never branch on the provider name themselves —
 * they call this (usually via `loadProviderForOrg()`) and use the
 * returned object.
 */
export function createProvider(
  creds: StoredWhatsAppCredentials,
): WhatsAppProvider {
  switch (creds.provider) {
    case "meta":
      return createMetaProvider(creds);
    case "twilio":
      return createTwilioProvider(creds);
    default: {
      const _exhaustive: never = creds;
      throw new Error(
        `Unknown WhatsApp provider: ${JSON.stringify(_exhaustive)}`,
      );
    }
  }
}
