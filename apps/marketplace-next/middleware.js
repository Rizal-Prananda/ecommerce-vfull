import { NextResponse } from "next/server";

import { COOKIE_NAME, verifySession } from "@/lib/auth";

export async function middleware(request) {
  const token = request.cookies.get(COOKIE_NAME)?.value;
  if (!token) return NextResponse.redirect(new URL("/login", request.url));

  try {
    await verifySession(token);
    return NextResponse.next();
  } catch {
    const res = NextResponse.redirect(new URL("/login", request.url));
    res.cookies.delete(COOKIE_NAME);
    return res;
  }
}

export const config = {
  matcher: ["/checkout/:path*", "/api/orders/:path*"],
};
