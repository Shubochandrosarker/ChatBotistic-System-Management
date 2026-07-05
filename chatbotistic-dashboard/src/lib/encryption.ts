import "server-only";
import crypto from "node:crypto";

/**
 * Credential encryption at rest — AES-256-GCM.
 *
 * Format: `<iv-hex>:<ciphertext-hex>:<authTag-hex>`
 *
 * GCM (not CBC) because unauthenticated ciphertext is malleable: an
 * attacker who can write rows (RLS bug, tampered backup) could flip
 * bits without decrypt throwing. GCM's 16-byte tag fails hard on any
 * tampering.
 *
 * `ENCRYPTION_KEY` must be 64 hex chars (32 bytes). Generate with:
 *   openssl rand -hex 32
 */

const GCM_IV_LENGTH = 12; // NIST-recommended IV length for GCM
const AUTH_TAG_LENGTH = 16;

function key(): Buffer {
  const hex = process.env.ENCRYPTION_KEY;
  if (!hex || hex.length !== 64) {
    throw new Error(
      "ENCRYPTION_KEY must be set to 64 hex characters (openssl rand -hex 32)"
    );
  }
  return Buffer.from(hex, "hex");
}

export function encrypt(text: string): string {
  const iv = crypto.randomBytes(GCM_IV_LENGTH);
  const cipher = crypto.createCipheriv("aes-256-gcm", key(), iv);
  let encrypted = cipher.update(text, "utf8", "hex");
  encrypted += cipher.final("hex");
  const authTag = cipher.getAuthTag();
  return `${iv.toString("hex")}:${encrypted}:${authTag.toString("hex")}`;
}

export function decrypt(encryptedText: string): string {
  const parts = encryptedText.split(":");
  if (parts.length !== 3) {
    throw new Error("Encrypted value has unrecognised format");
  }
  const [ivHex, ctHex, tagHex] = parts;
  const iv = Buffer.from(ivHex, "hex");
  if (iv.length !== GCM_IV_LENGTH) {
    throw new Error(`Encrypted value has unexpected IV length ${iv.length}`);
  }
  const authTag = Buffer.from(tagHex, "hex");
  if (authTag.length !== AUTH_TAG_LENGTH) {
    throw new Error(
      `Encrypted value has unexpected auth-tag length ${authTag.length}`
    );
  }
  const decipher = crypto.createDecipheriv("aes-256-gcm", key(), iv);
  decipher.setAuthTag(authTag);
  let decrypted = decipher.update(ctHex, "hex", "utf8");
  decrypted += decipher.final("utf8");
  return decrypted;
}
