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
    const url = new URL(request.url);
    const auth = request.headers.get("authorization") ?? request.headers.get("Authorization") ?? "";
    const pelangganId = request.headers.get("x-pelanggan-id") ?? request.headers.get("X-Pelanggan-Id") ?? "";
    const idFromQuery = String(url.searchParams.get("id_pelanggan") ?? "").trim();
    const id = idFromQuery || String(pelangganId ?? "").trim();
    const authOut = auth || (id ? `Bearer pelanggan:${id}` : "");
    const pelangganIdOut = String(pelangganId ?? "").trim() || id;
    if (!authOut) return json(401, { ok: false, message: "Unauthorized" });

    const res = await fetch(`http://127.0.0.1:8001/api/chat/history?${url.searchParams.toString()}`, {
      method: "GET",
      headers: {
        ...(authOut ? { Authorization: authOut } : {}),
        ...(pelangganIdOut ? { "X-Pelanggan-Id": pelangganIdOut } : {}),
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
