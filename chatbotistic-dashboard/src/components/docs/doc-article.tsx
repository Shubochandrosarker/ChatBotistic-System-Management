import { cn } from "@/lib/utils";
import Link from "next/link";
import { ArrowLeft, ArrowRight } from "lucide-react";
import type { HTMLAttributes, ReactNode } from "react";
import { docHref, neighbors } from "./docs-config";

/** Small content-formatting helpers shared by every doc page — plain
 * TSX content (no MDX dependency in this project) styled consistently. */
export function DocH2({ className, ...props }: HTMLAttributes<HTMLHeadingElement>) {
  return <h2 className={cn("mt-2 text-lg font-semibold text-foreground", className)} {...props} />;
}

export function DocP({ className, ...props }: HTMLAttributes<HTMLParagraphElement>) {
  return <p className={cn("text-muted-foreground", className)} {...props} />;
}

export function DocUL({ className, ...props }: HTMLAttributes<HTMLUListElement>) {
  return <ul className={cn("list-disc space-y-1.5 pl-5 text-muted-foreground", className)} {...props} />;
}

export function DocOL({ className, ...props }: HTMLAttributes<HTMLOListElement>) {
  return <ol className={cn("list-decimal space-y-1.5 pl-5 text-muted-foreground", className)} {...props} />;
}

export function DocCode({ className, ...props }: HTMLAttributes<HTMLElement>) {
  return (
    <code
      className={cn("rounded bg-muted px-1.5 py-0.5 font-mono text-[13px] text-foreground", className)}
      {...props}
    />
  );
}

export function DocCallout({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn("rounded-xl border border-border bg-muted/50 px-4 py-3 text-sm text-foreground", className)}
      {...props}
    />
  );
}

export function DocArticle({
  slug,
  title,
  description,
  children,
}: {
  slug: string;
  title: string;
  description?: string;
  children: ReactNode;
}) {
  const { prev, next } = neighbors(slug);

  return (
    <article className="flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-semibold tracking-tight text-foreground">{title}</h1>
        {description && <p className="mt-2 text-base text-muted-foreground">{description}</p>}
      </div>

      <div className="doc-prose flex flex-col gap-4 text-[15px] leading-relaxed text-foreground">{children}</div>

      <div className="mt-4 flex items-center justify-between gap-4 border-t border-border pt-5">
        {prev ? (
          <Link
            href={docHref(prev.slug)}
            className="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline"
          >
            <ArrowLeft className="h-4 w-4" />
            {prev.title}
          </Link>
        ) : (
          <span />
        )}
        {next ? (
          <Link
            href={docHref(next.slug)}
            className="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline"
          >
            {next.title}
            <ArrowRight className="h-4 w-4" />
          </Link>
        ) : (
          <span />
        )}
      </div>
    </article>
  );
}
