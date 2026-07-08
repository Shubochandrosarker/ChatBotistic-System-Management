import { type NextRequest, NextResponse } from "next/server";

/**
 * SSO token catcher.
 *
 * The WordPress side (chatbotistic-profile's SSO_Bridge hooked onto the
 * theme's `cb_dashboard_url` filter, plus the connector's "Open Full
 * Dashboard" button) appends `?token=<sso-token>` to whatever dashboard
 * URL it links to — usually the site root. The token is only consumed by
 * GET /api/sso/login, so without this hop the click would land on a page
 * that silently ignores the token and shows a logged-out screen.
 *
 * This proxy forwards any page request carrying `?token=` into the SSO
 * login route, which verifies it, provisions the org, and starts the
 * session. Invalid or expired tokens end up on /login with a readable
 * error — clicking through from WordPress again mints a fresh one.
 */
export function proxy(request: NextRequest) {
  const token = request.nextUrl.searchParams.get("token");
  if (!token) {
    return NextResponse.next();
  }

  const ssoUrl = new URL("/api/sso/login", request.nextUrl.origin);
  ssoUrl.searchParams.set("token", token);
  return NextResponse.redirect(ssoUrl);
}

export const config = {
  // Pages only — never intercept API routes (especially /api/sso/login
  // itself), Next internals, or static assets.
  matcher: ["/((?!api|_next/static|_next/image|favicon.ico|.*\\..*).*)"],
};
