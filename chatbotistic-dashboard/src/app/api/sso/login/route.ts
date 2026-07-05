import { supabaseAdmin } from "@/lib/supabase/admin";
import { provisionFromClaims } from "@/lib/sso/provision";
import { SsoTokenError, verifySsoToken } from "@/lib/sso/token";
import { type NextRequest, NextResponse } from "next/server";

/**
 * GET /api/sso/login?token=<sso-token>
 *
 * Entry point for the Memberistic + Licenseistic SSO bridge on
 * chatbotistic.com. The WordPress plugins redirect the browser here
 * with a short-lived, HMAC-signed token (see `src/lib/sso/token.ts`).
 *
 * 1. Verify the token signature + freshness.
 * 2. Ensure a Supabase auth user exists for the asserted email.
 * 3. Upsert the organization (keyed by the WordPress subject) with the
 *    latest plan/license entitlements.
 * 4. Ensure the user is an owner member of that org.
 * 5. Redirect the browser through Supabase's magic-link verifier,
 *    which sets the session cookie and lands on /dashboard.
 *
 * Every failure redirects to /login with an error message — a bad
 * token never results in a partially provisioned login.
 */

function loginErrorRedirect(request: NextRequest, message: string) {
  const url = new URL("/login", request.nextUrl.origin);
  url.searchParams.set("error", message);
  return NextResponse.redirect(url);
}

export async function GET(request: NextRequest) {
  const token = request.nextUrl.searchParams.get("token");
  if (!token) {
    return loginErrorRedirect(request, "Missing SSO token.");
  }

  const secret = process.env.SSO_SHARED_SECRET;
  if (!secret) {
    console.error("[sso/login] SSO_SHARED_SECRET is not configured");
    return loginErrorRedirect(request, "SSO is not configured on this deployment.");
  }
  const maxSkew = Number(process.env.SSO_MAX_SKEW_SECONDS ?? 300);

  let claims;
  try {
    claims = verifySsoToken(token, secret, maxSkew);
  } catch (err) {
    if (err instanceof SsoTokenError) {
      return loginErrorRedirect(request, err.message);
    }
    throw err;
  }

  const admin = supabaseAdmin();
  const siteUrl = process.env.NEXT_PUBLIC_SITE_URL ?? request.nextUrl.origin;
  const redirectTo = `${siteUrl.replace(/\/$/, "")}/dashboard`;

  // Ensure the auth user exists. createUser fails loudly only on real
  // errors — an "already registered" message is the expected path for
  // a returning user and is treated as success.
  const { error: createError } = await admin.auth.admin.createUser({
    email: claims.email,
    email_confirm: true,
    user_metadata: { full_name: claims.name ?? "" },
  });
  if (createError && !/already|registered|exists/i.test(createError.message)) {
    console.error("[sso/login] createUser failed:", createError.message);
    return loginErrorRedirect(request, `Failed to provision user: ${createError.message}`);
  }

  // The magic link both resolves the user id and bootstraps the
  // browser session when followed.
  const { data: linkData, error: linkError } = await admin.auth.admin.generateLink({
    type: "magiclink",
    email: claims.email,
    options: { redirectTo },
  });
  if (linkError || !linkData?.user || !linkData.properties?.action_link) {
    console.error("[sso/login] generateLink failed:", linkError?.message);
    return loginErrorRedirect(
      request,
      `Failed to start session: ${linkError?.message ?? "unknown error"}`
    );
  }

  try {
    await provisionFromClaims(admin, linkData.user.id, claims);
  } catch (err) {
    const message = err instanceof Error ? err.message : "Provisioning failed";
    console.error("[sso/login] provisioning failed:", message);
    return loginErrorRedirect(request, message);
  }

  return NextResponse.redirect(linkData.properties.action_link);
}
