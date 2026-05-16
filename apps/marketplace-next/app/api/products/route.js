const CMS_URL = process.env.NEXT_PUBLIC_CMS_URL ?? "http://127.0.0.1:8000";

function toSlug(product) {
  const raw = String(product?.title ?? "")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/(^-|-$)/g, "");
  const id = String(product?.id ?? "");
  return id ? `${id}-${raw || "item"}` : raw || "item";
}

export async function GET() {
  try {
    const res = await fetch(`${CMS_URL.replace(/\/$/, "")}/api/products`, {
      cache: "no-store",
    });
    const json = await res.json();
    const items = Array.isArray(json?.data) ? json.data : [];
    const normalized = items.map((p) => ({ ...p, slug: toSlug(p) }));
    return Response.json({ data: normalized });
  } catch {
    return Response.json({ data: [] });
  }
}
