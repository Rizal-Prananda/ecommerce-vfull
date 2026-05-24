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

type BodyItem = {
  id: unknown;
  title: unknown;
  price: unknown;
  qty: unknown;
  variant?: unknown;
};

type BodyCustomer = {
  user_id?: unknown;
  name?: unknown;
  email?: unknown;
  phone?: unknown;
  address?: unknown;
};

function safeInt(v: unknown, fallback = 0) {
  const n = typeof v === "number" ? v : Number(String(v ?? "").trim());
  if (!Number.isFinite(n)) return fallback;
  return Math.trunc(n);
}

function safeStr(v: unknown) {
  return String(v ?? "").trim();
}

function makeOrderId(now = new Date()) {
  const date = now.toISOString().slice(0, 10).replace(/-/g, "");
  const ts6 = String(Date.now() % 1_000_000).padStart(6, "0");
  const rnd3 = String(Math.floor(Math.random() * 1000)).padStart(3, "0");
  return `ORD-${date}-${ts6}${rnd3}`;
}

export const POST: APIRoute = async ({ request }) => {
  let db: Database.Database | null = null;
  try {
    const body = await request.json().catch(() => null);
    const itemsIn = Array.isArray(body?.items) ? (body.items as BodyItem[]) : [];
    const customer = (body?.customer ?? null) as BodyCustomer | null;

    if (!itemsIn.length) {
      return json(400, { success: false, message: "Items wajib diisi." });
    }

    const items = itemsIn
      .map((it) => {
        const productId = safeInt(it?.id, 0);
        const title = safeStr(it?.title);
        const variant = safeStr(it?.variant);
        const price = Math.max(0, safeInt(it?.price, 0));
        const qty = Math.max(1, safeInt(it?.qty, 1));
        if (!productId || !title) return null;
        return { productId, title, variant, price, qty, subtotal: price * qty };
      })
      .filter(Boolean) as { productId: number; title: string; variant: string; price: number; qty: number; subtotal: number }[];

    if (!items.length) {
      return json(400, { success: false, message: "Items wajib diisi." });
    }

    const subtotal = items.reduce((sum, it) => sum + it.subtotal, 0);
    const shipping = 10000;
    const total = subtotal + shipping;
    const orderId = makeOrderId();

    const userIdRaw = customer ? safeInt(customer.user_id, 0) : 0;
    const userId = userIdRaw > 0 ? userIdRaw : 0;

    const __dirname = path.dirname(fileURLToPath(import.meta.url));
    const dbPath = path.resolve(__dirname, "../../../../cms-laravel/database/database.sqlite");

    db = new Database(dbPath);
    db.pragma("journal_mode = WAL");
    db.pragma("foreign_keys = ON");

    const findUnpaidOrder = db.prepare(`
      SELECT id_order
      FROM Orders
      WHERE id_pelanggan = @id_pelanggan
        AND status IN ('UNPAID', 'PENDING')
      ORDER BY createdAt DESC
      LIMIT 1
    `);

    const insertOrder = db.prepare(`
      INSERT INTO Orders (id_pelanggan, order_no, status, total, createdAt)
      VALUES (@id_pelanggan, @order_no, @status, @total, datetime('now'))
    `);

    const updateOrder = db.prepare(`
      UPDATE Orders
      SET order_no = @order_no,
          status = @status,
          total = @total
      WHERE id_order = @id_order
    `);

    const deleteItems = db.prepare(`
      DELETE FROM OrderItems
      WHERE id_order = @id_order
    `);

    const insertItem = db.prepare(`
      INSERT INTO OrderItems (
        id_order, status, product_id, variant_id, title, size, image,
        unit_price, qty, line_total, createdAt, updatedAt
      )
      VALUES (
        @id_order, @status, @product_id, @variant_id, @title, @size, @image,
        @unit_price, @qty, @line_total, datetime('now'), datetime('now')
      )
    `);

    let idOrder = 0;
    if (userId > 0) {
      const unpaid = findUnpaidOrder.get({ id_pelanggan: userId }) as { id_order?: unknown } | undefined;
      const existingId = safeInt(unpaid?.id_order ?? 0, 0);
      if (existingId > 0) {
        idOrder = existingId;
        updateOrder.run({ id_order: idOrder, order_no: orderId, status: "UNPAID", total });
      }
    }

    if (!idOrder) {
      const orderRow = insertOrder.run({
        id_pelanggan: userId,
        order_no: orderId,
        status: "UNPAID",
        total,
      });
      idOrder = safeInt(orderRow?.lastInsertRowid ?? 0, 0);
    }

    if (!idOrder) {
      return json(500, { success: false, message: "Gagal membuat order." });
    }

    const writeOrder = db.transaction((rows: typeof items) => {
      deleteItems.run({ id_order: idOrder });
      for (const it of rows) {
        insertItem.run({
          id_order: idOrder,
          status: "UNPAID",
          product_id: it.productId,
          variant_id: null,
          title: it.title,
          size: it.variant,
          image: "",
          unit_price: it.price,
          qty: it.qty,
          line_total: it.subtotal,
        });
      }
    });

    writeOrder(items);

    return json(200, { success: true, order_id: orderId, redirect_url: `/pembayaran/${orderId}` });
  } catch {
    return json(500, { success: false, message: "Server error" });
  } finally {
    try {
      db?.close();
    } catch {}
  }
};
