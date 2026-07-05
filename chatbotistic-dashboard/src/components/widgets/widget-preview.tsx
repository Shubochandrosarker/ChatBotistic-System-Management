"use client";

import { cn } from "@/lib/utils";
import { AnimatePresence, motion } from "framer-motion";
import { MessageCircle, Send, X } from "lucide-react";
import { useEffect, useState } from "react";
import { useReducedMotion } from "@/components/motion/use-reduced-motion";
import type { AgentDraft, WidgetFormState } from "./widget-form-types";

export type PreviewMode = "desktop" | "mobile";

const SPRING = { type: "spring", stiffness: 380, damping: 32 } as const;
const COLOR_TRANSITION = { duration: 0.25, ease: [0.16, 1, 0.3, 1] } as const;

function initials(name: string): string {
  const parts = name.trim().split(/\s+/).filter(Boolean);
  if (parts.length === 0) return "?";
  return parts
    .slice(0, 2)
    .map((p) => p[0]?.toUpperCase() ?? "")
    .join("");
}

/**
 * Self-contained, real-time visual mimic of the embedded WhatsApp
 * widget. Reads straight off the same form state the editor tabs
 * mutate — no fetches, no debouncing, so every keystroke / color pick
 * is reflected immediately with a spring-eased transition.
 */
export function WidgetPreview({
  form,
  agents,
  mode = "desktop",
}: {
  form: WidgetFormState;
  agents: AgentDraft[];
  mode?: PreviewMode;
}) {
  const reduced = useReducedMotion();
  const [open, setOpen] = useState(form.autoOpen);

  // Keep the panel's open state honest when autoOpen is toggled from
  // the Appearance tab, without fighting a manual click afterwards.
  useEffect(() => {
    setOpen(form.autoOpen);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [form.autoOpen]);

  const faqs = agents.flatMap((a) => a.faqs.filter((f) => f.question.trim()));
  const visibleAgents = agents.filter((a) => a.name.trim());
  const isRight = form.position !== "left";

  const frameWidth = mode === "mobile" ? 360 : 640;

  return (
    <div className="flex flex-col items-center gap-3">
      <div
        className={cn(
          "relative overflow-hidden rounded-2xl border border-border bg-muted/40",
          mode === "mobile" ? "border-[10px] border-neutral-800 dark:border-neutral-700" : "border-border"
        )}
        style={{ width: frameWidth, height: mode === "mobile" ? 640 : 460, maxWidth: "100%" }}
      >
        {/* Mock page behind the widget so it reads as "on a real site" */}
        <div className="absolute inset-0 flex flex-col gap-3 overflow-hidden bg-background p-5">
          <div className="h-4 w-2/3 rounded-full bg-muted" />
          <div className="h-3 w-full rounded-full bg-muted/70" />
          <div className="h-3 w-5/6 rounded-full bg-muted/70" />
          <div className="mt-2 h-24 w-full rounded-xl bg-muted/50" />
          <div className="h-3 w-3/4 rounded-full bg-muted/70" />
          <div className="h-3 w-1/2 rounded-full bg-muted/70" />
        </div>

        {/* Chat panel */}
        <AnimatePresence>
          {open && (
            <motion.div
              key="panel"
              initial={reduced ? undefined : { opacity: 0, y: 16, scale: 0.96 }}
              animate={{ opacity: 1, y: 0, scale: 1 }}
              exit={reduced ? undefined : { opacity: 0, y: 16, scale: 0.96 }}
              transition={SPRING}
              className={cn(
                "absolute bottom-20 flex max-h-[calc(100%-6rem)] w-[85%] max-w-[300px] flex-col overflow-hidden rounded-2xl bg-card shadow-lift",
                isRight ? "right-4" : "left-4"
              )}
            >
              {/* Header */}
              <motion.div
                animate={{
                  background: `linear-gradient(135deg, ${form.landingPrimaryColor || "#25D366"}, ${
                    form.landingSecondaryColor || "#128C7E"
                  })`,
                }}
                transition={COLOR_TRANSITION}
                className="flex items-start justify-between gap-2 px-4 py-3.5 text-white"
              >
                <div className="min-w-0">
                  <p className="truncate text-sm font-semibold">{form.name || "Your Widget"}</p>
                  {form.legend && (
                    <p className="mt-0.5 truncate text-xs text-white/85">{form.legend}</p>
                  )}
                </div>
                <button
                  type="button"
                  onClick={() => setOpen(false)}
                  aria-label="Close preview panel"
                  className="mt-0.5 shrink-0 rounded-full p-0.5 text-white/85 transition-colors hover:bg-white/15 hover:text-white"
                >
                  <X className="h-4 w-4" />
                </button>
              </motion.div>

              {/* Body */}
              <div className="flex flex-1 flex-col gap-3 overflow-y-auto bg-muted/20 p-3">
                {/* Greeting bubble */}
                {form.widgetMessage && (
                  <motion.div
                    layout
                    className="max-w-[90%] rounded-2xl rounded-tl-sm bg-card px-3 py-2 text-xs text-foreground shadow-soft"
                  >
                    {form.widgetMessage}
                  </motion.div>
                )}

                {/* Agents */}
                {visibleAgents.length > 0 && (
                  <div className="flex flex-col gap-1.5">
                    {visibleAgents.map((a) => (
                      <motion.div
                        layout
                        key={a._key}
                        className="flex items-center gap-2 rounded-xl bg-card px-2.5 py-2 shadow-soft"
                      >
                        <motion.span
                          animate={{ backgroundColor: form.color || "#25D366" }}
                          transition={COLOR_TRANSITION}
                          className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[10px] font-semibold text-white"
                        >
                          {initials(a.name)}
                        </motion.span>
                        <div className="min-w-0">
                          <p className="truncate text-xs font-medium text-foreground">{a.name}</p>
                          {a.jobTitle && (
                            <p className="truncate text-[10px] text-muted-foreground">{a.jobTitle}</p>
                          )}
                        </div>
                      </motion.div>
                    ))}
                  </div>
                )}

                {/* FAQ chips */}
                {faqs.length > 0 && (
                  <div className="flex flex-wrap gap-1.5">
                    {faqs.slice(0, 4).map((f) => (
                      <motion.span
                        layout
                        key={f._key}
                        className="rounded-full border border-border bg-card px-2.5 py-1 text-[10px] text-foreground shadow-soft"
                      >
                        {f.question}
                      </motion.span>
                    ))}
                  </div>
                )}
              </div>

              {/* Start chat button */}
              <div className="border-t border-border bg-card p-2.5">
                <motion.button
                  type="button"
                  animate={{ backgroundColor: form.color || "#25D366" }}
                  transition={COLOR_TRANSITION}
                  className="flex w-full items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-xs font-medium text-white"
                >
                  <Send className="h-3.5 w-3.5" />
                  {form.buttonMessage || "Start chat"}
                </motion.button>
              </div>
            </motion.div>
          )}
        </AnimatePresence>

        {/* Floating launcher button */}
        <motion.button
          type="button"
          layout
          onClick={() => setOpen((o) => !o)}
          animate={{ backgroundColor: form.color || "#25D366" }}
          transition={COLOR_TRANSITION}
          whileHover={reduced ? undefined : { scale: 1.06 }}
          whileTap={reduced ? undefined : { scale: 0.94 }}
          aria-label={open ? "Close chat preview" : "Open chat preview"}
          className={cn(
            "absolute bottom-4 flex h-14 w-14 items-center justify-center rounded-full text-white shadow-lift",
            isRight ? "right-4" : "left-4"
          )}
        >
          <AnimatePresence mode="wait" initial={false}>
            <motion.span
              key={open ? "close" : "open"}
              initial={{ opacity: 0, rotate: -45, scale: 0.7 }}
              animate={{ opacity: 1, rotate: 0, scale: 1 }}
              exit={{ opacity: 0, rotate: 45, scale: 0.7 }}
              transition={{ duration: 0.15 }}
              className="flex"
            >
              {open ? <X className="h-6 w-6" /> : <MessageCircle className="h-6 w-6" />}
            </motion.span>
          </AnimatePresence>
        </motion.button>

        {!form.active && (
          <div className="absolute left-1/2 top-3 -translate-x-1/2 rounded-full bg-black/70 px-3 py-1 text-[10px] font-medium text-white">
            Widget inactive — preview only
          </div>
        )}
      </div>
      <p className="text-xs text-muted-foreground">
        {mode === "mobile" ? "Mobile preview" : "Desktop preview"} · click the bubble to toggle the panel
      </p>
    </div>
  );
}
