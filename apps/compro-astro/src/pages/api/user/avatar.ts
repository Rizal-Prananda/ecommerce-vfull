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

    const form = await request.formData().catch(() => null);
    if (!form) return json(400, { ok: false, message: "Form-data tidak valid" });

    const avatar = form.get("avatar");
    if (!(avatar instanceof File) || avatar.size <= 0) {
      return json(400, { ok: false, message: "File avatar tidak valid" });
    }

    const out = new FormData();
    out.set("avatar", avatar, avatar.name);

    const res = await fetch("http://localhost:8001/api/user/avatar", {
      method: "POST",
      headers: {
        ...(auth ? { Authorization: auth } : {}),
        ...(pelangganId ? { "X-Pelanggan-Id": pelangganId } : {}),
      },
      body: out,
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
