import { AuthBrandPanel } from "@/components/auth/brand-panel";
import type { Metadata } from "next";

// Auth pages should never be indexed — they'd compete with marketing
// pages on chatbotistic.com and offer nothing to a searcher who hasn't
// already signed up.
export const metadata: Metadata = {
  robots: { index: false, follow: false, nocache: true },
};

export default function AuthLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <div className="grid min-h-screen grid-cols-1 lg:grid-cols-2">
      <AuthBrandPanel />
      <div className="flex items-center justify-center p-6 sm:p-10">
        <div className="w-full max-w-sm">{children}</div>
      </div>
    </div>
  );
}
