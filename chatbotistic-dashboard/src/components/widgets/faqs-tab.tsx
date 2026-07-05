"use client";

import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/input";
import { Select } from "@/components/ui/select";
import { StaggerItem, StaggerList } from "@/components/motion/stagger-list";
import { AnimatePresence, motion } from "framer-motion";
import { Plus, Trash2 } from "lucide-react";
import { useEffect, useState, type Dispatch, type SetStateAction } from "react";
import type { TochatResource } from "@/lib/tochat/types";
import { ConfirmDialog } from "./confirm-dialog";
import { WidgetTextarea } from "./widget-textarea";
import { extractId, nextKey, type AgentDraft, type FaqDraft } from "./widget-form-types";

interface FaqApiShape {
  faq?: TochatResource;
  error?: string;
}

export function FaqsTab({
  agents,
  setAgents,
}: {
  agents: AgentDraft[];
  setAgents: Dispatch<SetStateAction<AgentDraft[]>>;
}) {
  const persistedAgents = agents.filter((a) => a.id);
  const [selectedKey, setSelectedKey] = useState<string>(persistedAgents[0]?._key ?? "");
  const [savingKey, setSavingKey] = useState<string | null>(null);
  const [deletingFaq, setDeletingFaq] = useState<FaqDraft | null>(null);
  const [errors, setErrors] = useState<Record<string, string>>({});

  useEffect(() => {
    if (!persistedAgents.some((a) => a._key === selectedKey)) {
      setSelectedKey(persistedAgents[0]?._key ?? "");
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [agents]);

  const selectedAgent = persistedAgents.find((a) => a._key === selectedKey) ?? null;

  function updateFaqs(agentKey: string, updater: (faqs: FaqDraft[]) => FaqDraft[]) {
    setAgents((prev) =>
      prev.map((a) => (a._key === agentKey ? { ...a, faqs: updater(a.faqs) } : a))
    );
  }

  function addFaq() {
    if (!selectedAgent) return;
    updateFaqs(selectedAgent._key, (faqs) => [
      ...faqs,
      { question: "", answer: "", _key: nextKey("faq") },
    ]);
  }

  function editFaq(faqKey: string, patch: Partial<FaqDraft>) {
    if (!selectedAgent) return;
    updateFaqs(selectedAgent._key, (faqs) => faqs.map((f) => (f._key === faqKey ? { ...f, ...patch } : f)));
  }

  async function saveFaq(faq: FaqDraft) {
    if (!selectedAgent?.id) return;
    if (!faq.question.trim() || !faq.answer.trim()) {
      setErrors((e) => ({ ...e, [faq._key]: "Both question and answer are required." }));
      return;
    }
    setErrors((e) => {
      const next = { ...e };
      delete next[faq._key];
      return next;
    });
    setSavingKey(faq._key);
    try {
      const payload = { question: faq.question.trim(), answer: faq.answer.trim() };
      const url = faq.id
        ? `/api/tochat/operators/${encodeURIComponent(selectedAgent.id)}/faq-groups?id=${encodeURIComponent(faq.id)}`
        : `/api/tochat/operators/${encodeURIComponent(selectedAgent.id)}/faq-groups`;
      const res = await fetch(url, {
        method: faq.id ? "PUT" : "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const body = (await res.json().catch(() => ({}))) as FaqApiShape;
      if (!res.ok) throw new Error(body.error || "Failed to save FAQ.");
      const savedId = body.faq ? extractId(body.faq) : faq.id;
      editFaq(faq._key, { id: savedId || faq.id });
    } catch (err) {
      setErrors((e) => ({ ...e, [faq._key]: err instanceof Error ? err.message : "Failed to save FAQ." }));
    } finally {
      setSavingKey(null);
    }
  }

  async function deleteFaq(faq: FaqDraft) {
    if (!selectedAgent) return;
    if (faq.id && selectedAgent.id) {
      const res = await fetch(
        `/api/tochat/operators/${encodeURIComponent(selectedAgent.id)}/faq-groups?id=${encodeURIComponent(faq.id)}`,
        { method: "DELETE" }
      );
      if (!res.ok) {
        const body = await res.json().catch(() => ({}));
        throw new Error(body.error || "Failed to delete FAQ.");
      }
    }
    updateFaqs(selectedAgent._key, (faqs) => faqs.filter((f) => f._key !== faq._key));
  }

  if (persistedAgents.length === 0) {
    return (
      <p className="rounded-xl border border-dashed border-border bg-muted/20 p-4 text-sm text-muted-foreground">
        Add and save at least one agent on the Agents tab before building FAQs.
      </p>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <div>
        <Label htmlFor="faq-agent-select">Agent</Label>
        <Select
          id="faq-agent-select"
          value={selectedKey}
          onChange={(e) => setSelectedKey(e.target.value)}
        >
          {persistedAgents.map((a) => (
            <option key={a._key} value={a._key}>
              {a.name || "Untitled agent"}
            </option>
          ))}
        </Select>
      </div>

      {selectedAgent && (
        <>
          {selectedAgent.faqs.length === 0 ? (
            <p className="rounded-xl border border-dashed border-border bg-muted/20 p-4 text-sm text-muted-foreground">
              No FAQs yet for {selectedAgent.name || "this agent"}.
            </p>
          ) : (
            <StaggerList className="flex flex-col gap-3">
              <AnimatePresence initial={false}>
                {selectedAgent.faqs.map((faq) => (
                  <StaggerItem key={faq._key}>
                    <motion.div
                      layout
                      exit={{ opacity: 0, height: 0, marginBottom: 0 }}
                      className="flex flex-col gap-3 rounded-xl border border-border bg-card p-4"
                    >
                      <div>
                        <Label htmlFor={`faq-q-${faq._key}`}>Question</Label>
                        <WidgetTextarea
                          id={`faq-q-${faq._key}`}
                          value={faq.question}
                          onChange={(e) => editFaq(faq._key, { question: e.target.value })}
                          rows={2}
                          placeholder="What are your business hours?"
                        />
                      </div>
                      <div>
                        <Label htmlFor={`faq-a-${faq._key}`}>Answer</Label>
                        <WidgetTextarea
                          id={`faq-a-${faq._key}`}
                          value={faq.answer}
                          onChange={(e) => editFaq(faq._key, { answer: e.target.value })}
                          rows={2}
                          placeholder="We're online Monday to Friday, 9am-6pm."
                        />
                      </div>

                      {errors[faq._key] && (
                        <p className="text-xs text-destructive">{errors[faq._key]}</p>
                      )}

                      <div className="flex items-center justify-end gap-2">
                        <Button
                          type="button"
                          variant="destructive"
                          size="sm"
                          onClick={() => setDeletingFaq(faq)}
                        >
                          <Trash2 className="h-3.5 w-3.5" /> Remove
                        </Button>
                        <Button
                          type="button"
                          variant="primary"
                          size="sm"
                          loading={savingKey === faq._key}
                          onClick={() => saveFaq(faq)}
                        >
                          {faq.id ? "Save changes" : "Save FAQ"}
                        </Button>
                      </div>
                    </motion.div>
                  </StaggerItem>
                ))}
              </AnimatePresence>
            </StaggerList>
          )}

          <Button type="button" variant="outline" onClick={addFaq} className="self-start">
            <Plus className="h-4 w-4" /> Add FAQ
          </Button>
        </>
      )}

      {deletingFaq && (
        <ConfirmDialog
          open={!!deletingFaq}
          onOpenChange={(open) => !open && setDeletingFaq(null)}
          title="Remove this FAQ?"
          description="This question and answer will no longer show on the widget."
          onConfirm={() => deleteFaq(deletingFaq)}
        />
      )}
    </div>
  );
}
