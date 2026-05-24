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

    const normalizeThumb = (raw: unknown) => {
      const v = String(raw ?? "").trim();
      if (!v) return v;

      const toProxy = (pathAndQuery: string) => {
        const cleaned = String(pathAndQuery || "").trim();
        if (!cleaned) return cleaned;
        if (cleaned === "/cms" || cleaned.startsWith("/cms/")) return cleaned;
        if (cleaned === "cms" || cleaned.startsWith("cms/")) return `/${cleaned}`;
        return cleaned.startsWith("/") ? `/cms${cleaned}` : `/cms/${cleaned}`;
      };

      if (v.startsWith("http://") || v.startsWith("https://")) {
        try {
          const u = new URL(v);
          const cms = new URL(String(CMS_URL));
          const sameHost = u.hostname === cms.hostname && String(u.port || "") === String(cms.port || "");
          const isLoopback = u.hostname === "localhost" || u.hostname === "127.0.0.1";
          const cmsLoopback = cms.hostname === "localhost" || cms.hostname === "127.0.0.1";
          if (sameHost || (isLoopback && cmsLoopback)) {
            return toProxy(`${u.pathname}${u.search}`);
          }
          return v;
        } catch {
          return v;
        }
      }

      if (v.startsWith("products/")) return toProxy(`/product-media/${v}`);
      if (v.startsWith("/product-media/")) return toProxy(v);
      if (v.startsWith("/storage/")) return toProxy(v);
      if (v.startsWith("/uploads/")) return toProxy(v);
      if (v.startsWith("/")) return toProxy(v);
      return toProxy(v);
    };

    if (Array.isArray((payload as any)?.data)) {
      (payload as any).data = (payload as any).data.map((o: any) => ({
        ...o,
        thumbnail: normalizeThumb(o?.thumbnail),
        items: Array.isArray(o?.items)
          ? o.items.map((it: any) => ({
              ...it,
              thumbnail: normalizeThumb(it?.thumbnail),
            }))
          : o?.items,
      }));
    }

    return new Response(JSON.stringify(payload), { status: res.status, headers: { "Content-Type": "application/json" } });
  } catch {
    return json(500, { ok: false, message: "Server error" });
  }
};
