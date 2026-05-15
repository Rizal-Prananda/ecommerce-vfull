import { SignJWT, jwtVerify } from "jose";

const COOKIE_NAME = "session";

function getJwtSecret() {
  const raw = process.env.JWT_SECRET ?? "dev-secret-change-me";
  return new TextEncoder().encode(raw);
}

export async function signSession(payload) {
  const secret = getJwtSecret();
  return await new SignJWT(payload)
    .setProtectedHeader({ alg: "HS256" })
    .setIssuedAt()
    .setExpirationTime("7d")
    .sign(secret);
}

export async function verifySession(token) {
  const secret = getJwtSecret();
  const { payload } = await jwtVerify(token, secret);
  return payload;
}

export function sessionCookieOptions() {
  return {
    httpOnly: true,
    secure: process.env.NODE_ENV === "production",
    sameSite: "lax",
    path: "/",
  };
}

export { COOKIE_NAME };

