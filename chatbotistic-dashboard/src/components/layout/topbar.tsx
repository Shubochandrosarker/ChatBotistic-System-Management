"use client";

import { createClient } from "@/lib/supabase/client";
import { ThemeToggle } from "@/components/ui/theme-toggle";
import { cn } from "@/lib/utils";
import { AnimatePresence, motion } from "framer-motion";
import { Bell, ChevronDown, LogOut, Menu, Settings, User } from "lucide-react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useRef, useState } from "react";

export function Topbar({
  orgName,
  userEmail,
  onOpenMobileSidebar,
}: {
  orgName: string;
  userEmail: string;
  onOpenMobileSidebar: () => void;
}) {
  return (
    <header className="flex h-14 shrink-0 items-center justify-between gap-3 border-b border-border bg-card px-4 sm:px-6">
      <div className="flex items-center gap-3">
        <button
          type="button"
          onClick={onOpenMobileSidebar}
          aria-label="Open menu"
          className="rounded-lg p-2 text-muted-foreground hover:bg-muted lg:hidden"
        >
          <Menu className="h-5 w-5" />
        </button>
        <OrgSwitcher orgName={orgName} />
      </div>

      <div className="flex items-center gap-1.5">
        <button
          type="button"
          aria-label="Notifications"
          className="relative flex h-10 w-10 items-center justify-center rounded-xl text-foreground transition-colors hover:bg-muted"
        >
          <Bell className="h-5 w-5" />
        </button>
        <ThemeToggle />
        <UserMenu userEmail={userEmail} />
      </div>
    </header>
  );
}

function OrgSwitcher({ orgName }: { orgName: string }) {
  return (
    <button
      type="button"
      className="flex items-center gap-1.5 rounded-xl px-2.5 py-1.5 text-sm font-medium text-foreground transition-colors hover:bg-muted"
    >
      <span className="max-w-[10rem] truncate sm:max-w-xs">{orgName}</span>
      <ChevronDown className="h-4 w-4 text-muted-foreground" />
    </button>
  );
}

function UserMenu({ userEmail }: { userEmail: string }) {
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);
  const router = useRouter();

  useEffect(() => {
    if (!open) return;
    function onPointerDown(e: PointerEvent) {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    }
    function onKeyDown(e: KeyboardEvent) {
      if (e.key === "Escape") setOpen(false);
    }
    document.addEventListener("pointerdown", onPointerDown);
    document.addEventListener("keydown", onKeyDown);
    return () => {
      document.removeEventListener("pointerdown", onPointerDown);
      document.removeEventListener("keydown", onKeyDown);
    };
  }, [open]);

  async function signOut() {
    const supabase = createClient();
    await supabase.auth.signOut();
    router.push("/login");
    router.refresh();
  }

  return (
    <div className="relative" ref={ref}>
      <button
        type="button"
        onClick={() => setOpen((v) => !v)}
        aria-haspopup="menu"
        aria-expanded={open}
        className="flex h-10 w-10 items-center justify-center rounded-full bg-accent text-accent-foreground transition-transform active:scale-95"
      >
        <User className="h-[18px] w-[18px]" />
      </button>

      <AnimatePresence>
        {open && (
          <motion.div
            role="menu"
            initial={{ opacity: 0, scale: 0.96, y: -6 }}
            animate={{ opacity: 1, scale: 1, y: 0 }}
            exit={{ opacity: 0, scale: 0.96, y: -6 }}
            transition={{ duration: 0.15, ease: "easeOut" }}
            className={cn(
              "absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-2xl border border-border bg-popover text-popover-foreground shadow-lift"
            )}
          >
            <div className="border-b border-border px-3.5 py-3">
              <p className="truncate text-sm font-medium">{userEmail}</p>
            </div>
            <Link
              role="menuitem"
              href="/settings"
              onClick={() => setOpen(false)}
              className="flex items-center gap-2.5 px-3.5 py-2.5 text-sm text-foreground transition-colors hover:bg-muted"
            >
              <Settings className="h-4 w-4" />
              Settings
            </Link>
            <button
              role="menuitem"
              type="button"
              onClick={signOut}
              className="flex w-full items-center gap-2.5 px-3.5 py-2.5 text-left text-sm text-destructive transition-colors hover:bg-muted"
            >
              <LogOut className="h-4 w-4" />
              Sign out
            </button>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
