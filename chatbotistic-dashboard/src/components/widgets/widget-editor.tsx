"use client";

import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input, Label } from "@/components/ui/input";
import { Switch } from "@/components/ui/switch";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { AnimatePresence, motion } from "framer-motion";
import { Check, Code2, Laptop, Minus, Plus, Smartphone } from "lucide-react";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { cn } from "@/lib/utils";
import type { TochatResource } from "@/lib/tochat/types";
import { AgentsTab } from "./agents-tab";
import { ColorField } from "./color-field";
import { EmbedDialog } from "./embed-dialog";
import { FaqsTab } from "./faqs-tab";
import { WidgetPreview, type PreviewMode } from "./widget-preview";
import { WidgetTextarea } from "./widget-textarea";
import type { AgentDraft, WidgetFormState } from "./widget-form-types";

type EditorTab = "identity" | "messages" | "appearance" | "agents" | "faqs";

interface WidgetApiShape {
  widget?: TochatResource & { tochat_widget_id?: string };
  error?: string;
}

export function WidgetEditor({
  mode,
  widgetId,
  initialForm,
  initialAgents,
}: {
  mode: "create" | "edit";
  widgetId: string | null;
  initialForm: WidgetFormState;
  initialAgents: AgentDraft[];
}) {
  const router = useRouter();
  const [form, setForm] = useState<WidgetFormState>(initialForm);
  const [agents, setAgents] = useState<AgentDraft[]>(initialAgents);
  const [currentWidgetId, setCurrentWidgetId] = useState<string | null>(widgetId);
  const [tab, setTab] = useState<EditorTab>("identity");
  const [previewMode, setPreviewMode] = useState<PreviewMode>("desktop");
  const [saving, setSaving] = useState(false);
  const [saveError, setSaveError] = useState<string | null>(null);
  const [saveSuccess, setSaveSuccess] = useState(false);
  const [embedOpen, setEmbedOpen] = useState(false);

  function set<K extends keyof WidgetFormState>(key: K, value: WidgetFormState[K]) {
    setForm((f) => ({ ...f, [key]: value }));
  }

  async function handleSave() {
    if (!form.name.trim()) {
      setSaveError("Give your widget a name before saving.");
      setTab("identity");
      return;
    }
    setSaving(true);
    setSaveError(null);
    const payload = { ...form };
    try {
      if (currentWidgetId) {
        const res = await fetch(`/api/tochat/widgets/${encodeURIComponent(currentWidgetId)}`, {
          method: "PUT",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });
        const body = (await res.json().catch(() => ({}))) as WidgetApiShape;
        if (!res.ok) throw new Error(body.error || "Failed to save widget.");
      } else {
        const res = await fetch("/api/tochat/widgets", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });
        const body = (await res.json().catch(() => ({}))) as WidgetApiShape;
        if (!res.ok) throw new Error(body.error || "Failed to create widget.");
        const newId = body.widget?.tochat_widget_id || (body.widget ? String(body.widget.uuid ?? body.widget.id ?? "") : "");
        if (newId) {
          setCurrentWidgetId(newId);
          router.replace(`/widgets/${newId}`);
        }
      }
      setSaveSuccess(true);
      setTimeout(() => setSaveSuccess(false), 2500);
    } catch (err) {
      setSaveError(err instanceof Error ? err.message : "Failed to save widget.");
    } finally {
      setSaving(false);
    }
  }

  return (
    <div className="flex flex-col gap-6">
      <div className="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">
            {mode === "create" ? "Create widget" : form.name || "Edit widget"}
          </h1>
          <p className="mt-1 text-sm text-muted-foreground">
            Design how your WhatsApp widget looks and behaves — changes preview live on the right.
          </p>
        </div>
        <div className="flex items-center gap-2">
          {currentWidgetId && (
            <Button type="button" variant="outline" onClick={() => setEmbedOpen(true)}>
              <Code2 className="h-4 w-4" /> Embed
            </Button>
          )}
          <Button type="button" variant="primary" loading={saving} onClick={handleSave}>
            <AnimatePresence mode="wait" initial={false}>
              {saveSuccess ? (
                <motion.span
                  key="saved"
                  initial={{ opacity: 0, scale: 0.7 }}
                  animate={{ opacity: 1, scale: 1 }}
                  exit={{ opacity: 0, scale: 0.7 }}
                  className="flex items-center gap-1.5"
                >
                  <Check className="h-4 w-4" /> Saved
                </motion.span>
              ) : (
                <motion.span
                  key="save"
                  initial={{ opacity: 0, scale: 0.7 }}
                  animate={{ opacity: 1, scale: 1 }}
                  exit={{ opacity: 0, scale: 0.7 }}
                >
                  {currentWidgetId ? "Save changes" : "Create widget"}
                </motion.span>
              )}
            </AnimatePresence>
          </Button>
        </div>
      </div>

      {saveError && (
        <motion.div
          initial={{ opacity: 0, y: -6 }}
          animate={{ opacity: 1, y: 0 }}
          className="rounded-xl border border-destructive/30 bg-destructive/10 px-4 py-2.5 text-sm text-destructive"
        >
          {saveError}
        </motion.div>
      )}

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_400px]">
        {/* Left: settings panel */}
        <Card>
          <CardContent className="pt-5">
            <Tabs value={tab} onValueChange={(v) => setTab(v as EditorTab)}>
              <TabsList>
                <TabsTrigger value="identity">Identity</TabsTrigger>
                <TabsTrigger value="messages">Messages</TabsTrigger>
                <TabsTrigger value="appearance">Appearance</TabsTrigger>
                <TabsTrigger value="agents">Agents</TabsTrigger>
                <TabsTrigger value="faqs">FAQs</TabsTrigger>
              </TabsList>

              <div className="pt-5">
                <TabsContent value="identity" className="flex flex-col gap-4">
                  <div>
                    <Label htmlFor="widget-name">Name</Label>
                    <Input
                      id="widget-name"
                      value={form.name}
                      onChange={(e) => set("name", e.target.value)}
                      placeholder="My website widget"
                    />
                  </div>
                  <div>
                    <Label htmlFor="widget-legend">Legend text</Label>
                    <Input
                      id="widget-legend"
                      value={form.legend}
                      onChange={(e) => set("legend", e.target.value)}
                      placeholder="Usually replies within minutes"
                    />
                  </div>
                  <div className="flex items-center justify-between rounded-xl bg-muted/30 px-3 py-2.5">
                    <div>
                      <p className="text-sm font-medium text-foreground">Active</p>
                      <p className="text-xs text-muted-foreground">Show this widget on your site.</p>
                    </div>
                    <Switch
                      checked={form.active}
                      onCheckedChange={(v) => set("active", v)}
                      aria-label="Widget active"
                    />
                  </div>
                </TabsContent>

                <TabsContent value="messages" className="flex flex-col gap-4">
                  <div>
                    <Label htmlFor="widget-message">Greeting message</Label>
                    <WidgetTextarea
                      id="widget-message"
                      value={form.widgetMessage}
                      onChange={(e) => set("widgetMessage", e.target.value)}
                      rows={3}
                    />
                  </div>
                  <div>
                    <Label htmlFor="button-message">Chat button label</Label>
                    <WidgetTextarea
                      id="button-message"
                      value={form.buttonMessage}
                      onChange={(e) => set("buttonMessage", e.target.value)}
                      rows={2}
                    />
                  </div>
                  <div>
                    <Label htmlFor="offline-message">Offline message</Label>
                    <WidgetTextarea
                      id="offline-message"
                      value={form.offlineMessage}
                      onChange={(e) => set("offlineMessage", e.target.value)}
                      rows={3}
                    />
                  </div>
                </TabsContent>

                <TabsContent value="appearance" className="flex flex-col gap-4">
                  <ColorField
                    id="color-main"
                    label="Button color"
                    value={form.color}
                    onChange={(v) => set("color", v)}
                  />
                  <ColorField
                    id="color-primary"
                    label="Header gradient — primary"
                    value={form.landingPrimaryColor}
                    onChange={(v) => set("landingPrimaryColor", v)}
                  />
                  <ColorField
                    id="color-secondary"
                    label="Header gradient — secondary"
                    value={form.landingSecondaryColor}
                    onChange={(v) => set("landingSecondaryColor", v)}
                  />

                  <div>
                    <Label htmlFor="theme-stepper">Theme</Label>
                    <div className="flex items-center gap-2">
                      <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        onClick={() => set("theme", Math.max(1, form.theme - 1))}
                        aria-label="Decrease theme"
                      >
                        <Minus className="h-4 w-4" />
                      </Button>
                      <Input
                        id="theme-stepper"
                        readOnly
                        value={form.theme}
                        className="w-16 text-center"
                      />
                      <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        onClick={() => set("theme", form.theme + 1)}
                        aria-label="Increase theme"
                      >
                        <Plus className="h-4 w-4" />
                      </Button>
                    </div>
                  </div>

                  <div className="flex items-center justify-between rounded-xl bg-muted/30 px-3 py-2.5">
                    <div>
                      <p className="text-sm font-medium text-foreground">Position</p>
                      <p className="text-xs text-muted-foreground">Which side the bubble docks to.</p>
                    </div>
                    <div className="flex items-center gap-2 text-sm">
                      <span className={cn(form.position === "left" ? "font-medium text-foreground" : "text-muted-foreground")}>
                        Left
                      </span>
                      <Switch
                        checked={form.position === "right"}
                        onCheckedChange={(v) => set("position", v ? "right" : "left")}
                        aria-label="Widget position"
                      />
                      <span className={cn(form.position === "right" ? "font-medium text-foreground" : "text-muted-foreground")}>
                        Right
                      </span>
                    </div>
                  </div>

                  <div className="flex items-center justify-between rounded-xl bg-muted/30 px-3 py-2.5">
                    <div>
                      <p className="text-sm font-medium text-foreground">Auto-open</p>
                      <p className="text-xs text-muted-foreground">Expand the panel automatically on load.</p>
                    </div>
                    <Switch
                      checked={form.autoOpen}
                      onCheckedChange={(v) => set("autoOpen", v)}
                      aria-label="Auto-open widget"
                    />
                  </div>
                </TabsContent>

                <TabsContent value="agents">
                  <AgentsTab widgetId={currentWidgetId} agents={agents} setAgents={setAgents} />
                </TabsContent>

                <TabsContent value="faqs">
                  <FaqsTab agents={agents} setAgents={setAgents} />
                </TabsContent>
              </div>
            </Tabs>
          </CardContent>
        </Card>

        {/* Right: live preview */}
        <div className="lg:sticky lg:top-6 lg:self-start">
          <Card>
            <CardContent className="flex flex-col items-center gap-4 pt-5">
              <div className="inline-flex items-center gap-1 rounded-xl bg-muted/40 p-1">
                <button
                  type="button"
                  onClick={() => setPreviewMode("desktop")}
                  aria-pressed={previewMode === "desktop"}
                  className={cn(
                    "flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors",
                    previewMode === "desktop"
                      ? "bg-card text-foreground shadow-soft"
                      : "text-muted-foreground hover:text-foreground"
                  )}
                >
                  <Laptop className="h-3.5 w-3.5" /> Desktop
                </button>
                <button
                  type="button"
                  onClick={() => setPreviewMode("mobile")}
                  aria-pressed={previewMode === "mobile"}
                  className={cn(
                    "flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors",
                    previewMode === "mobile"
                      ? "bg-card text-foreground shadow-soft"
                      : "text-muted-foreground hover:text-foreground"
                  )}
                >
                  <Smartphone className="h-3.5 w-3.5" /> Mobile
                </button>
              </div>

              <WidgetPreview form={form} agents={agents} mode={previewMode} />
            </CardContent>
          </Card>
        </div>
      </div>

      {currentWidgetId && (
        <EmbedDialog open={embedOpen} onOpenChange={setEmbedOpen} widgetId={currentWidgetId} />
      )}
    </div>
  );
}
