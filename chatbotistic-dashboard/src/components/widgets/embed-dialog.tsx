"use client";

import { Button } from "@/components/ui/button";
import { Dialog, DialogBody, DialogFooter, DialogHeader } from "@/components/ui/dialog";
import { AnimatePresence, motion } from "framer-motion";
import { Check, Copy } from "lucide-react";
import { useState } from "react";

const TOCHAT_LOAD_JS_BASE = "https://services.tochat.be/widget";

export function EmbedDialog({
  open,
  onOpenChange,
  widgetId,
}: {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  widgetId: string;
}) {
  const [copied, setCopied] = useState(false);
  const snippet = `<script async src="${TOCHAT_LOAD_JS_BASE}/${widgetId}/load.js"></script>`;

  async function copy() {
    try {
      await navigator.clipboard.writeText(snippet);
      setCopied(true);
      setTimeout(() => setCopied(false), 1800);
    } catch {
      // Clipboard API unavailable (permissions/non-HTTPS) — the
      // snippet is still selectable text, so this is non-fatal.
    }
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange} className="max-w-xl">
      <DialogHeader onClose={() => onOpenChange(false)}>Embed this widget</DialogHeader>
      <DialogBody className="flex flex-col gap-4">
        <div>
          <p className="mb-1.5 text-sm font-medium text-foreground">Script snippet</p>
          <div className="flex items-start gap-2 rounded-xl border border-border bg-muted/40 p-3">
            <code className="flex-1 select-all break-all text-xs text-foreground">{snippet}</code>
            <Button
              type="button"
              variant="outline"
              size="sm"
              onClick={copy}
              aria-label="Copy embed snippet"
              className="shrink-0"
            >
              <AnimatePresence mode="wait" initial={false}>
                {copied ? (
                  <motion.span
                    key="check"
                    initial={{ opacity: 0, scale: 0.6 }}
                    animate={{ opacity: 1, scale: 1 }}
                    exit={{ opacity: 0, scale: 0.6 }}
                    transition={{ duration: 0.15 }}
                    className="flex items-center gap-1.5 text-success"
                  >
                    <Check className="h-3.5 w-3.5" /> Copied
                  </motion.span>
                ) : (
                  <motion.span
                    key="copy"
                    initial={{ opacity: 0, scale: 0.6 }}
                    animate={{ opacity: 1, scale: 1 }}
                    exit={{ opacity: 0, scale: 0.6 }}
                    transition={{ duration: 0.15 }}
                    className="flex items-center gap-1.5"
                  >
                    <Copy className="h-3.5 w-3.5" /> Copy
                  </motion.span>
                )}
              </AnimatePresence>
            </Button>
          </div>
          <p className="mt-1.5 text-xs text-muted-foreground">
            Paste this right before the closing <code>&lt;/body&gt;</code> tag of any page you want the
            widget to appear on.
          </p>
        </div>

        <div className="rounded-xl border border-border bg-muted/20 p-3">
          <p className="text-sm font-medium text-foreground">Using WordPress?</p>
          <p className="mt-1 text-xs text-muted-foreground">
            Install the <span className="font-medium text-foreground">chatbotistic-widget</span> plugin,
            then activate it with your license key — it handles the embed for you, so you can skip pasting
            the script tag manually.
          </p>
        </div>
      </DialogBody>
      <DialogFooter>
        <Button type="button" variant="primary" onClick={() => onOpenChange(false)}>
          Done
        </Button>
      </DialogFooter>
    </Dialog>
  );
}
