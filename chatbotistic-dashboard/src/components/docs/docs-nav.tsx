"use client";

import { cn } from "@/lib/utils";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { DOC_SECTIONS, docHref } from "./docs-config";

export function DocsNav() {
  const pathname = usePathname();

  return (
    <nav className="flex flex-col gap-0.5">
      {DOC_SECTIONS.map((section) => {
        const href = docHref(section.slug);
        const active = pathname === href;
        return (
          <Link
            key={section.slug}
            href={href}
            aria-current={active ? "page" : undefined}
            className={cn(
              "rounded-lg px-3 py-2 text-sm transition-colors",
              active
                ? "bg-accent font-medium text-accent-foreground"
                : "text-muted-foreground hover:bg-muted hover:text-foreground"
            )}
          >
            {section.title}
          </Link>
        );
      })}
    </nav>
  );
}
