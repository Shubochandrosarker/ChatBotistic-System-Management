import { cn } from "@/lib/utils";
import { forwardRef, type TextareaHTMLAttributes } from "react";

export interface WidgetTextareaProps
  extends TextareaHTMLAttributes<HTMLTextAreaElement> {
  error?: boolean;
}

/**
 * Textarea styled to match `@/components/ui/input` — no shared
 * `ui/textarea` exists yet, so this local twin lives with the widget
 * editor that needs it instead of touching `src/components/ui/*`.
 */
export const WidgetTextarea = forwardRef<HTMLTextAreaElement, WidgetTextareaProps>(
  ({ className, error, ...props }, ref) => {
    return (
      <textarea
        ref={ref}
        aria-invalid={error || undefined}
        className={cn(
          "min-h-[84px] w-full resize-y rounded-xl border border-input bg-background px-3 py-2 text-sm text-foreground",
          "placeholder:text-muted-foreground",
          "transition-shadow duration-150 ease-out",
          "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:border-transparent",
          "disabled:cursor-not-allowed disabled:opacity-50",
          error && "border-destructive focus-visible:ring-destructive",
          className
        )}
        {...props}
      />
    );
  }
);
WidgetTextarea.displayName = "WidgetTextarea";
