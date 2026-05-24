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

export const POST: APIRoute = async ({ params }) => {
  let db: Database.Database | null = null;
  try {
    const orderId = String(params?.id ?? "").trim();
    if (!orderId) {
      return json(400, { success: false, message: "order id wajib diisi." });
    }

    const __dirname = path.dirname(fileURLToPath(import.meta.url));
    const dbPath = path.resolve(__dirname, "../../../../../../cms-laravel/database/database.sqlite");

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
      return json(409, { success: false, message: "Pesanan sudah dibayar, tidak bisa dibatalkan." });
    }
    if (status !== "UNPAID") {
      return json(409, { success: false, message: "Pesanan hanya bisa dibatalkan saat status masih UNPAID." });
    }

    const tx = db.transaction(() => {
      const cur = db!
        .prepare(
          `
          SELECT status
          FROM Orders
          WHERE id_order = ?
          LIMIT 1
        `
        )
        .get(idOrder) as { status?: unknown } | undefined;
      const curStatus = String(cur?.status ?? "").toUpperCase();
      if (curStatus === "PAID") {
        throw new Error("paid");
      }
      if (curStatus !== "UNPAID") {
        throw new Error("not_unpaid");
      }

      db!.prepare(
        `
        UPDATE Orders
        SET status = 'CANCELLED'
        WHERE id_order = ?
      `
      ).run(idOrder);

      db!.prepare(
        `
        UPDATE OrderItems
        SET status = 'CANCELLED', updatedAt = datetime('now')
        WHERE id_order = ?
      `
      ).run(idOrder);
    });

    try {
      tx();
    } catch (e) {
      if (e instanceof Error && e.message === "paid") {
        return json(409, { success: false, message: "Pesanan sudah dibayar, tidak bisa dibatalkan." });
      }
      if (e instanceof Error && e.message === "not_unpaid") {
        return json(409, { success: false, message: "Pesanan hanya bisa dibatalkan saat status masih UNPAID." });
      }
      throw e;
    }

    return json(200, { success: true, order_id: orderId });
  } catch {
    return json(500, { success: false, message: "Server error" });
  } finally {
    try {
      db?.close();
    } catch {}
  }
};
