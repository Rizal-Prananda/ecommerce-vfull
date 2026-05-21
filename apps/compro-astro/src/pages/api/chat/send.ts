import type { APIRoute } from "astro";

export const prerender = false;

type SendBody = {
  id_pelanggan?: unknown;
  pesan?: unknown;
};

function json(status: number, data: unknown) {
  return new Response(JSON.stringify(data), {
    status,
    headers: { "Content-Type": "application/json" },
  });
}

function isNonEmptyString(v: unknown): v is string {
  return typeof v === "string" && v.trim().length > 0;
}

export const POST: APIRoute = async ({ request }) => {
  try {
    let body: SendBody | null = null;

    // 1) Coba JSON (paling umum dari fetch)
    try {
      body = (await request.clone().json()) as SendBody;
    } catch {}

    // 2) Coba form-data (kalau suatu saat pakai upload)
    if (!body) {
      try {
        const form = await request.clone().formData();
        body = {
          id_pelanggan: form.get("id_pelanggan"),
          pesan: form.get("pesan"),
        };
      } catch {}
    }

    // 3) Fallback text: bisa JSON string atau x-www-form-urlencoded
    if (!body) {
      try {
        const raw = await request.clone().text();
        const t = String(raw ?? "").trim();
        if (t) {
          if (t.startsWith("{") && t.endsWith("}")) {
            body = JSON.parse(t) as SendBody;
          } else {
            const params = new URLSearchParams(t);
            if (params.has("id_pelanggan") || params.has("pesan")) {
              body = {
                id_pelanggan: params.get("id_pelanggan"),
                pesan: params.get("pesan"),
              };
            }
          }
        }
      } catch {}
    }
    if (!body) return json(400, { ok: false, message: "Body tidak valid (harus JSON atau form-data)" });

    const id = typeof body.id_pelanggan === "number" ? body.id_pelanggan : Number(body.id_pelanggan);
    const pesan = isNonEmptyString(body.pesan) ? body.pesan.trim() : "";

    if (!Number.isInteger(id) || id <= 0) return json(400, { ok: false, message: "id_pelanggan tidak valid" });
    if (!pesan) return json(400, { ok: false, message: "Pesan wajib diisi" });
    if (pesan.length > 2000) return json(400, { ok: false, message: "Pesan terlalu panjang" });

    const auth = request.headers.get("authorization") ?? request.headers.get("Authorization") ?? "";
    const pelangganId = request.headers.get("x-pelanggan-id") ?? request.headers.get("X-Pelanggan-Id") ?? "";
    const authOut = auth || `Bearer pelanggan:${id}`;
    const pelangganIdOut = pelangganId || String(id);

    const res = await fetch("http://127.0.0.1:8001/api/chat/send", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        ...(authOut ? { Authorization: authOut } : {}),
        ...(pelangganIdOut ? { "X-Pelanggan-Id": pelangganIdOut } : {}),
      },
      body: JSON.stringify({ id_pelanggan: id, pesan }),
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
