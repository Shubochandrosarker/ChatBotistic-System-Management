import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

/** Format a number with thin locale separators (1,234). */
export function formatNumber(n: number): string {
  return new Intl.NumberFormat("en-US").format(n);
}

/** "2026-07-05T…" → "Jul 5, 2026" */
export function formatDate(iso: string | null | undefined): string {
  if (!iso) return "—";
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return "—";
  return d.toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
}

export function formatDateTime(iso: string | null | undefined): string {
  if (!iso) return "—";
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return "—";
  return d.toLocaleString("en-US", {
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
  });
}

/** Loose E.164 check — "+" followed by 7–15 digits. */
export function isE164(phone: string): boolean {
  return /^\+?[1-9]\d{6,14}$/.test(phone.replace(/[\s()-]/g, ""));
}

/** Normalize a phone number to digits-only for wa.me deep links. */
export function waDigits(phone: string): string {
  return phone.replace(/[^\d]/g, "");
}
