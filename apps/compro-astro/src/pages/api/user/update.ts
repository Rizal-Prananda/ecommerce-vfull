import type { APIRoute } from "astro";

export const prerender = false;

function json(status: number, data: unknown) {
  return new Response(JSON.stringify(data), {
    status,
    headers: { "Content-Type": "application/json" },
  });
}

export const POST: APIRoute = async ({ request }) => {
  try {
    const auth = request.headers.get("authorization") ?? request.headers.get("Authorization") ?? "";
    const pelangganId = request.headers.get("x-pelanggan-id") ?? request.headers.get("X-Pelanggan-Id") ?? "";

    const body = await request.json().catch(() => null);
    if (!body || typeof body !== "object") return json(400, { ok: false, message: "Body tidak valid" });

    const res = await fetch("http://localhost:8001/api/user/update", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        ...(auth ? { Authorization: auth } : {}),
        ...(pelangganId ? { "X-Pelanggan-Id": pelangganId } : {}),
      },
      body: JSON.stringify(body),
    });

    const raw = await res.text();
    return new Response(raw, {
      status: res.status,
      headers: { "Content-Type": "application/json" },
    });
  } catch {
    return json(500, { ok: false, message: "Server error" });
  }
};
