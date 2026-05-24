import type { APIRoute } from "astro";

export const prerender = false;

const CMS_URL = import.meta.env.PUBLIC_CMS_URL ?? "http://localhost:8001";

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

    const res = await fetch(`${String(CMS_URL).replace(/\/$/, "")}/api/orders`, {
      method: "GET",
      headers: {
        ...(auth ? { Authorization: auth } : {}),
        ...(pelangganId ? { "X-Pelanggan-Id": pelangganId } : {}),
      },
    });

    const payload = await res.json().catch(() => null);
    if (!payload || typeof payload !== "object") {
      const body = await res.text().catch(() => "");
      return new Response(body, { status: res.status, headers: { "Content-Type": "application/json" } });
    }

    const cmsOrigin = (() => {
      try {
        return new URL(String(CMS_URL)).origin;
      } catch {
        return String(CMS_URL).replace(/\/$/, "");
      }
    })();

    const normalizeThumb = (raw: unknown) => {
      const v = String(raw ?? "").trim();
      if (!v) return v;

      if (v.startsWith("http://") || v.startsWith("https://")) {
        try {
          const u = new URL(v);
          const cms = new URL(cmsOrigin);
          const isLoopback = u.hostname === "localhost" || u.hostname === "127.0.0.1";
          const cmsLoopback = cms.hostname === "localhost" || cms.hostname === "127.0.0.1";
          if (isLoopback && cmsLoopback) u.hostname = cms.hostname;
          if (u.port && cms.port) u.port = cms.port;
          return u.toString();
        } catch {
          return v;
        }
      }

      if (v.startsWith("products/")) return `${cmsOrigin}/product-media/${v}`;
      if (v.startsWith("/product-media/")) return `${cmsOrigin}${v}`;
      if (v.startsWith("/")) return `${cmsOrigin}${v}`;
      return `${cmsOrigin}/${v}`;
    };

    if (Array.isArray((payload as any)?.data)) {
      (payload as any).data = (payload as any).data.map((o: any) => ({
        ...o,
        thumbnail: normalizeThumb(o?.thumbnail),
      }));
    }

    return new Response(JSON.stringify(payload), { status: res.status, headers: { "Content-Type": "application/json" } });
  } catch {
    return json(500, { ok: false, message: "Server error" });
  }
};
