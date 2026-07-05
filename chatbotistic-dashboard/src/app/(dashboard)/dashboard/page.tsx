import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { StaggerItem, StaggerList } from "@/components/motion/stagger-list";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Dashboard",
};

const STAT_CARDS = ["Leads today", "Active widgets", "Messages this month", "Open conversations"];

// Placeholder home — verifies the shell renders around a route. The
// real dashboard (charts, live stats) is built by another pass.
export default function DashboardPage() {
  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-semibold tracking-tight">Dashboard</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Welcome back — here&apos;s what&apos;s happening across your widgets.
        </p>
      </div>

      <StaggerList className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {STAT_CARDS.map((label) => (
          <StaggerItem key={label}>
            <Card>
              <CardHeader>
                <p className="text-sm font-medium text-muted-foreground">{label}</p>
              </CardHeader>
              <CardContent className="flex flex-col gap-2 pt-3">
                <Skeleton className="h-8 w-20" />
                <Skeleton className="h-3 w-28" />
              </CardContent>
            </Card>
          </StaggerItem>
        ))}
      </StaggerList>

      <Card>
        <CardHeader>
          <p className="text-sm font-medium text-muted-foreground">Recent activity</p>
        </CardHeader>
        <CardContent className="flex flex-col gap-3 pt-3">
          <Skeleton className="h-4 w-full" />
          <Skeleton className="h-4 w-5/6" />
          <Skeleton className="h-4 w-2/3" />
        </CardContent>
      </Card>
    </div>
  );
}
