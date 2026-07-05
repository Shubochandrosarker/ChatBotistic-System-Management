import { WidgetEditor } from "@/components/widgets/widget-editor";
import { DEFAULT_WIDGET_FORM } from "@/components/widgets/widget-form-types";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Create widget",
};

export default function NewWidgetPage() {
  return (
    <WidgetEditor mode="create" widgetId={null} initialForm={DEFAULT_WIDGET_FORM} initialAgents={[]} />
  );
}
