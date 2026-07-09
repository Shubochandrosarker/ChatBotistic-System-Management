import { LandingHero } from "@/components/landing/landing-hero";
import { LandingFeatures } from "@/components/landing/landing-features";
import type { Metadata } from "next";

// This is the app, not the marketing site — chatbotistic.com already
// owns pricing/features SEO. This page exists so a direct visit or an
// "Open Dashboard" click without an SSO token (e.g. the token expired,
// or chatbotistic-profile's SSO bridge is inactive) lands somewhere
// real instead of a 404, with a clear way into the product either way.
export const metadata: Metadata = {
  title: "Chatbotistic Dashboard",
  robots: { index: false, follow: true },
};

export default function RootPage() {
  return (
    <div className="min-h-screen bg-background">
      <LandingHero />
      <LandingFeatures />
    </div>
  );
}
