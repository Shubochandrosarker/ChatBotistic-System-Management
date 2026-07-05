"use client";

import { cn } from "@/lib/utils";
import { AnimatePresence, motion } from "framer-motion";
import {
  BookOpen,
  CalendarCheck,
  ChevronsLeft,
  FileText,
  Inbox,
  LayoutDashboard,
  LayoutGrid,
  Megaphone,
  MessageSquareText,
  Settings,
  Users,
  X,
} from "lucide-react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import type { ReactNode } from "react";

interface NavItem {
  href: string;
  label: string;
  icon: ReactNode;
}

const NAV_ITEMS: NavItem[] = [
  { href: "/dashboard", label: "Dashboard", icon: <LayoutDashboard className="h-[18px] w-[18px]" /> },
  { href: "/widgets", label: "Widgets", icon: <LayoutGrid className="h-[18px] w-[18px]" /> },
  { href: "/landing-pages", label: "Landing Pages", icon: <FileText className="h-[18px] w-[18px]" /> },
  { href: "/bookings", label: "Bookings", icon: <CalendarCheck className="h-[18px] w-[18px]" /> },
  { href: "/leads", label: "Leads", icon: <Users className="h-[18px] w-[18px]" /> },
  { href: "/inbox", label: "Inbox", icon: <Inbox className="h-[18px] w-[18px]" /> },
  { href: "/campaigns", label: "Campaigns", icon: <Megaphone className="h-[18px] w-[18px]" /> },
  { href: "/settings", label: "Settings", icon: <Settings className="h-[18px] w-[18px]" /> },
  { href: "/docs", label: "Docs", icon: <BookOpen className="h-[18px] w-[18px]" /> },
];

function isActive(pathname: string, href: string) {
  return href === "/dashboard" ? pathname === href : pathname.startsWith(href);
}

function Brand({ collapsed }: { collapsed: boolean }) {
  return (
    <Link
      href="/dashboard"
      className="flex h-14 shrink-0 items-center gap-2.5 px-4"
    >
      <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary text-primary-foreground">
        <MessageSquareText className="h-[18px] w-[18px]" />
      </span>
      <AnimatePresence initial={false}>
        {!collapsed && (
          <motion.span
            initial={{ opacity: 0, width: 0 }}
            animate={{ opacity: 1, width: "auto" }}
            exit={{ opacity: 0, width: 0 }}
            transition={{ duration: 0.15 }}
            className="overflow-hidden whitespace-nowrap text-[15px] font-semibold tracking-tight"
          >
            Chatbotistic
          </motion.span>
        )}
      </AnimatePresence>
    </Link>
  );
}

function NavLinks({ collapsed, onNavigate }: { collapsed: boolean; onNavigate?: () => void }) {
  const pathname = usePathname();
  return (
    <nav className="flex flex-1 flex-col gap-0.5 overflow-y-auto px-2 py-2">
      {NAV_ITEMS.map((item) => {
        const active = isActive(pathname, item.href);
        return (
          <Link
            key={item.href}
            href={item.href}
            onClick={onNavigate}
            aria-current={active ? "page" : undefined}
            title={collapsed ? item.label : undefined}
            className={cn(
              "group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors",
              active
                ? "bg-accent text-accent-foreground"
                : "text-muted-foreground hover:bg-muted hover:text-foreground"
            )}
          >
            <span className="shrink-0">{item.icon}</span>
            <AnimatePresence initial={false}>
              {!collapsed && (
                <motion.span
                  initial={{ opacity: 0, width: 0 }}
                  animate={{ opacity: 1, width: "auto" }}
                  exit={{ opacity: 0, width: 0 }}
                  transition={{ duration: 0.15 }}
                  className="overflow-hidden whitespace-nowrap"
                >
                  {item.label}
                </motion.span>
              )}
            </AnimatePresence>
          </Link>
        );
      })}
    </nav>
  );
}

export function Sidebar({
  collapsed,
  onToggleCollapse,
  mobileOpen,
  onCloseMobile,
}: {
  collapsed: boolean;
  onToggleCollapse: () => void;
  mobileOpen: boolean;
  onCloseMobile: () => void;
}) {
  return (
    <>
      {/* Desktop sidebar */}
      <motion.aside
        animate={{ width: collapsed ? 72 : 240 }}
        transition={{ type: "spring", stiffness: 400, damping: 38 }}
        className="hidden shrink-0 flex-col border-r border-border bg-sidebar lg:flex"
      >
        <Brand collapsed={collapsed} />
        <NavLinks collapsed={collapsed} />
        <div className="border-t border-border p-2">
          <button
            type="button"
            onClick={onToggleCollapse}
            aria-label={collapsed ? "Expand sidebar" : "Collapse sidebar"}
            className="flex w-full items-center justify-center gap-2 rounded-xl p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
          >
            <motion.span
              animate={{ rotate: collapsed ? 180 : 0 }}
              transition={{ duration: 0.2 }}
              className="flex"
            >
              <ChevronsLeft className="h-[18px] w-[18px]" />
            </motion.span>
          </button>
        </div>
      </motion.aside>

      {/* Mobile drawer */}
      <AnimatePresence>
        {mobileOpen && (
          <div className="fixed inset-0 z-40 lg:hidden">
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              transition={{ duration: 0.15 }}
              className="absolute inset-0 bg-black/40"
              onClick={onCloseMobile}
              aria-hidden
            />
            <motion.aside
              initial={{ x: "-100%" }}
              animate={{ x: 0 }}
              exit={{ x: "-100%" }}
              transition={{ type: "spring", stiffness: 420, damping: 40 }}
              className="absolute inset-y-0 left-0 flex w-64 flex-col bg-sidebar shadow-lift"
            >
              <div className="flex items-center justify-between">
                <Brand collapsed={false} />
                <button
                  type="button"
                  onClick={onCloseMobile}
                  aria-label="Close menu"
                  className="mr-3 rounded-lg p-2 text-muted-foreground hover:bg-muted"
                >
                  <X className="h-5 w-5" />
                </button>
              </div>
              <NavLinks collapsed={false} onNavigate={onCloseMobile} />
            </motion.aside>
          </div>
        )}
      </AnimatePresence>
    </>
  );
}
