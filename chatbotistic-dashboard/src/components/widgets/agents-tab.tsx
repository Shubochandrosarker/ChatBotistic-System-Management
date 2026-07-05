"use client";

import { Button } from "@/components/ui/button";
import { Input, Label } from "@/components/ui/input";
import { StaggerItem, StaggerList } from "@/components/motion/stagger-list";
import { AnimatePresence, motion } from "framer-motion";
import { Check, Plus, Trash2 } from "lucide-react";
import { useState, type Dispatch, type SetStateAction } from "react";
import { isE164 } from "@/lib/utils";
import type { TochatResource } from "@/lib/tochat/types";
import { ConfirmDialog } from "./confirm-dialog";
import { extractId, nextKey, type AgentDraft } from "./widget-form-types";

interface OperatorApiShape {
  operator?: TochatResource;
  error?: string;
}

export function AgentsTab({
  widgetId,
  agents,
  setAgents,
}: {
  widgetId: string | null;
  agents: AgentDraft[];
  setAgents: Dispatch<SetStateAction<AgentDraft[]>>;
}) {
  const [savingKey, setSavingKey] = useState<string | null>(null);
  const [deletingAgent, setDeletingAgent] = useState<AgentDraft | null>(null);
  const [errors, setErrors] = useState<Record<string, string>>({});

  function updateAgent(key: string, patch: Partial<AgentDraft>) {
    setAgents((prev) => prev.map((a) => (a._key === key ? { ...a, ...patch } : a)));
  }

  function addAgent() {
    setAgents((prev) => [
      ...prev,
      { name: "", number: "", jobTitle: "", greeting: "", faqs: [], _key: nextKey("agent") },
    ]);
  }

  async function saveAgent(agent: AgentDraft) {
    if (!widgetId) return;
    if (!agent.name.trim()) {
      setErrors((e) => ({ ...e, [agent._key]: "Name is required." }));
      return;
    }
    if (agent.number.trim() && !isE164(agent.number)) {
      setErrors((e) => ({ ...e, [agent._key]: "Phone must be a valid E.164 number, e.g. +14155550100." }));
      return;
    }
    setErrors((e) => {
      const next = { ...e };
      delete next[agent._key];
      return next;
    });
    setSavingKey(agent._key);
    try {
      const payload = {
        name: agent.name.trim(),
        number: agent.number.trim(),
        jobTitle: agent.jobTitle.trim(),
        greeting: agent.greeting.trim(),
      };
      const url = agent.id
        ? `/api/tochat/widgets/${encodeURIComponent(widgetId)}/operators?id=${encodeURIComponent(agent.id)}`
        : `/api/tochat/widgets/${encodeURIComponent(widgetId)}/operators`;
      const res = await fetch(url, {
        method: agent.id ? "PUT" : "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const body = (await res.json().catch(() => ({}))) as OperatorApiShape;
      if (!res.ok) throw new Error(body.error || "Failed to save agent.");
      const savedId = body.operator ? extractId(body.operator) : agent.id;
      updateAgent(agent._key, { id: savedId || agent.id });
    } catch (err) {
      setErrors((e) => ({
        ...e,
        [agent._key]: err instanceof Error ? err.message : "Failed to save agent.",
      }));
    } finally {
      setSavingKey(null);
    }
  }

  async function deleteAgent(agent: AgentDraft) {
    if (agent.id && widgetId) {
      const res = await fetch(
        `/api/tochat/widgets/${encodeURIComponent(widgetId)}/operators?id=${encodeURIComponent(agent.id)}`,
        { method: "DELETE" }
      );
      if (!res.ok) {
        const body = await res.json().catch(() => ({}));
        throw new Error(body.error || "Failed to delete agent.");
      }
    }
    setAgents((prev) => prev.filter((a) => a._key !== agent._key));
  }

  if (!widgetId) {
    return (
      <p className="rounded-xl border border-dashed border-border bg-muted/20 p-4 text-sm text-muted-foreground">
        Save your widget details first — agents are attached to a saved widget.
      </p>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      {agents.length === 0 ? (
        <p className="rounded-xl border border-dashed border-border bg-muted/20 p-4 text-sm text-muted-foreground">
          No agents yet. Add the WhatsApp numbers that should receive chats from this widget.
        </p>
      ) : (
        <StaggerList className="flex flex-col gap-3">
          <AnimatePresence initial={false}>
            {agents.map((agent) => (
              <StaggerItem key={agent._key}>
                <motion.div
                  layout
                  exit={{ opacity: 0, height: 0, marginBottom: 0 }}
                  className="flex flex-col gap-3 rounded-xl border border-border bg-card p-4"
                >
                  <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                      <Label htmlFor={`agent-name-${agent._key}`}>Name</Label>
                      <Input
                        id={`agent-name-${agent._key}`}
                        value={agent.name}
                        onChange={(e) => updateAgent(agent._key, { name: e.target.value })}
                        placeholder="Jamie Rivera"
                      />
                    </div>
                    <div>
                      <Label htmlFor={`agent-number-${agent._key}`}>WhatsApp number (E.164)</Label>
                      <Input
                        id={`agent-number-${agent._key}`}
                        value={agent.number}
                        onChange={(e) => updateAgent(agent._key, { number: e.target.value })}
                        placeholder="+14155550100"
                      />
                    </div>
                    <div>
                      <Label htmlFor={`agent-title-${agent._key}`}>Job title</Label>
                      <Input
                        id={`agent-title-${agent._key}`}
                        value={agent.jobTitle}
                        onChange={(e) => updateAgent(agent._key, { jobTitle: e.target.value })}
                        placeholder="Support lead"
                      />
                    </div>
                    <div>
                      <Label htmlFor={`agent-greeting-${agent._key}`}>Greeting message</Label>
                      <Input
                        id={`agent-greeting-${agent._key}`}
                        value={agent.greeting}
                        onChange={(e) => updateAgent(agent._key, { greeting: e.target.value })}
                        placeholder="Hey! I'm Jamie, happy to help."
                      />
                    </div>
                  </div>

                  {errors[agent._key] && (
                    <p className="text-xs text-destructive">{errors[agent._key]}</p>
                  )}

                  <div className="flex items-center justify-end gap-2">
                    <Button
                      type="button"
                      variant="destructive"
                      size="sm"
                      onClick={() => setDeletingAgent(agent)}
                    >
                      <Trash2 className="h-3.5 w-3.5" /> Remove
                    </Button>
                    <Button
                      type="button"
                      variant="primary"
                      size="sm"
                      loading={savingKey === agent._key}
                      onClick={() => saveAgent(agent)}
                    >
                      <Check className="h-3.5 w-3.5" />
                      {agent.id ? "Save changes" : "Save agent"}
                    </Button>
                  </div>
                </motion.div>
              </StaggerItem>
            ))}
          </AnimatePresence>
        </StaggerList>
      )}

      <Button type="button" variant="outline" onClick={addAgent} className="self-start">
        <Plus className="h-4 w-4" /> Add agent
      </Button>

      {deletingAgent && (
        <ConfirmDialog
          open={!!deletingAgent}
          onOpenChange={(open) => !open && setDeletingAgent(null)}
          title="Remove this agent?"
          description={`"${deletingAgent.name || "This agent"}" will no longer appear on the widget, and their FAQs will be removed too.`}
          onConfirm={() => deleteAgent(deletingAgent)}
        />
      )}
    </div>
  );
}
