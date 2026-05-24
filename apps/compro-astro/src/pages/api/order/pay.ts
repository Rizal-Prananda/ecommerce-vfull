import type { APIRoute } from "astro";

import Database from "better-sqlite3";
import path from "path";
import { fileURLToPath } from "url";

export const prerender = false;

function json(status: number, data: unknown) {
  return new Response(JSON.stringify(data), {
    status,
    headers: { "Content-Type": "application/json" },
  });
}

function safeStr(v: unknown) {
  return String(v ?? "").trim();
}

export const POST: APIRoute = async ({ request }) => {
  let db: Database.Database | null = null;
  try {
    const body = await request.json().catch(() => null);
    const orderId = safeStr(body?.order_id);
    const method = safeStr(body?.method) || "manual";

    if (!orderId) {
      return json(400, { success: false, message: "order_id wajib diisi." });
    }

    const __dirname = path.dirname(fileURLToPath(import.meta.url));
    const dbPath = path.resolve(__dirname, "../../../../../cms-laravel/database/database.sqlite");

    db = new Database(dbPath);
    db.pragma("journal_mode = WAL");
    db.pragma("foreign_keys = ON");

    const order = db
      .prepare(
        `
        SELECT id_order, status
        FROM Orders
        WHERE order_no = ?
        LIMIT 1
      `
      )
      .get(orderId) as { id_order?: unknown; status?: unknown } | undefined;

    const idOrder = Number(order?.id_order ?? 0);
    if (!Number.isFinite(idOrder) || idOrder <= 0) {
      return json(404, { success: false, message: "Order tidak ditemukan." });
    }

    const status = String(order?.status ?? "").toUpperCase();
    if (status === "PAID") {
      return json(200, { success: true, order_id: orderId, redirect_url: `/invoice/${orderId}`, method });
    }

    const payTx = db.transaction(() => {
      db.prepare(
        `
        UPDATE Orders
        SET status = 'PAID'
        WHERE id_order = ?
      `
      ).run(idOrder);

      db.prepare(
        `
        UPDATE OrderItems
        SET status = 'PAID', updatedAt = datetime('now')
        WHERE id_order = ?
      `
      ).run(idOrder);
    });

    payTx();

    return json(200, { success: true, order_id: orderId, redirect_url: `/invoice/${orderId}`, method });
  } catch {
    return json(500, { success: false, message: "Server error" });
  } finally {
    try {
      db?.close();
    } catch {}
  }
};

