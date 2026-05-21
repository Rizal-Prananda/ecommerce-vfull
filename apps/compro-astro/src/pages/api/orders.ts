import type { APIRoute } from "astro";

export const prerender = false;

function json(status: number, data: unknown) {
  return new Response(JSON.stringify(data), {
    status,
    headers: { "Content-Type": "application/json" },
  });
}

export const GET: APIRoute = async ({ request }) => {
  try {
    const auth = request.headers.get("authorization") ?? request.headers.get("Authorization") ?? "";
    const pelangganId = request.headers.get("x-pelanggan-id") ?? request.headers.get("X-Pelanggan-Id") ?? "";

    const res = await fetch("http://localhost:8001/api/orders", {
      method: "GET",
      headers: {
        ...(auth ? { Authorization: auth } : {}),
        ...(pelangganId ? { "X-Pelanggan-Id": pelangganId } : {}),
      },
    });

    const body = await res.text();
    return new Response(body, {
      status: res.status,
      headers: { "Content-Type": "application/json" },
    });
  } catch {
    return json(500, { ok: false, message: "Server error" });
  }
};
