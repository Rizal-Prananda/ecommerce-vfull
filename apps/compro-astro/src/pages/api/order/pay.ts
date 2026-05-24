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

class HttpError extends Error {
  status: number;
  data?: unknown;
  constructor(status: number, message: string, data?: unknown) {
    super(message);
    this.status = status;
    this.data = data;
  }
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
    const hasMovements =
      !!db
        .prepare(
          `
          SELECT 1
          FROM sqlite_master
          WHERE type = 'table' AND name = 'product_stock_movements'
          LIMIT 1
        `
        )
        .get();

    const order = db
      .prepare(
        `
        SELECT id_order, status, id_pelanggan
        FROM Orders
        WHERE order_no = ?
        LIMIT 1
      `
      )
      .get(orderId) as { id_order?: unknown; status?: unknown; id_pelanggan?: unknown } | undefined;

    const idOrder = Number(order?.id_order ?? 0);
    if (!Number.isFinite(idOrder) || idOrder <= 0) {
      return json(404, { success: false, message: "Order tidak ditemukan." });
    }

    const status = String(order?.status ?? "").toUpperCase();
    if (status === "PAID") {
      return json(200, { success: true, order_id: orderId, redirect_url: `/invoice/${orderId}`, method });
    }

    const payTx = db.transaction(() => {
      const current = db!
        .prepare(
          `
          SELECT status
          FROM Orders
          WHERE id_order = ?
          LIMIT 1
        `
        )
        .get(idOrder) as { status?: unknown } | undefined;
      const currentStatus = String(current?.status ?? "").toUpperCase();
      if (currentStatus === "PAID") {
        return;
      }

      const orderItems = db!
        .prepare(
          `
          SELECT product_id, variant_id, qty
          FROM OrderItems
          WHERE id_order = ?
        `
        )
        .all(idOrder) as { product_id?: unknown; variant_id?: unknown; qty?: unknown }[];

      const rawItems = Array.isArray(orderItems) ? orderItems : [];
      if (!rawItems.length) {
        throw new HttpError(422, "Item order kosong.");
      }

      const productIds: number[] = [];
      const variantIds: number[] = [];

      for (const it of rawItems) {
        const productId = Number(it?.product_id ?? 0);
        const variantId = it?.variant_id === null || it?.variant_id === undefined ? null : Number(it?.variant_id);
        if (Number.isFinite(productId) && productId > 0) productIds.push(Math.trunc(productId));
        if (variantId !== null && Number.isFinite(variantId) && variantId > 0) variantIds.push(Math.trunc(variantId));
      }

      const uniq = (arr: number[]) => Array.from(new Set(arr));
      const uniqProducts = uniq(productIds);
      const uniqVariants = uniq(variantIds);

      const productStockMap = new Map<number, number>();
      if (uniqProducts.length) {
        const rows = db!
          .prepare(
            `
            SELECT id, stock
            FROM products
            WHERE id IN (${uniqProducts.map(() => "?").join(",")})
          `
          )
          .all(...uniqProducts) as { id?: unknown; stock?: unknown }[];
        for (const r of rows) {
          const id = Number(r?.id ?? 0);
          const stock = Number(r?.stock ?? 0);
          if (Number.isFinite(id) && id > 0) productStockMap.set(Math.trunc(id), Math.max(0, Math.trunc(Number.isFinite(stock) ? stock : 0)));
        }
      }

      const variantMap = new Map<number, { product_id: number; stock: number }>();
      if (uniqVariants.length) {
        const rows = db!
          .prepare(
            `
            SELECT id, product_id, stock
            FROM product_variants
            WHERE id IN (${uniqVariants.map(() => "?").join(",")})
          `
          )
          .all(...uniqVariants) as { id?: unknown; product_id?: unknown; stock?: unknown }[];
        for (const r of rows) {
          const id = Number(r?.id ?? 0);
          const productId = Number(r?.product_id ?? 0);
          const stock = Number(r?.stock ?? 0);
          if (Number.isFinite(id) && id > 0 && Number.isFinite(productId) && productId > 0) {
            variantMap.set(Math.trunc(id), {
              product_id: Math.trunc(productId),
              stock: Math.max(0, Math.trunc(Number.isFinite(stock) ? stock : 0)),
            });
          }
        }
      }

      const neededProducts = new Map<number, number>();
      const neededVariants = new Map<number, { productId: number; qty: number }>();

      for (const it of rawItems) {
        const productId = Math.trunc(Number(it?.product_id ?? 0));
        const qtyIn = Math.max(1, Math.trunc(Number(it?.qty ?? 1)));
        const rawVariantId = it?.variant_id;
        const variantId = rawVariantId === null || rawVariantId === undefined ? null : Math.trunc(Number(rawVariantId));

        if (!Number.isFinite(productId) || productId <= 0) continue;

        if (variantId !== null && Number.isFinite(variantId) && variantId > 0) {
          const v = variantMap.get(variantId);
          if (!v || v.product_id !== productId) {
            throw new HttpError(422, "Variant tidak valid.");
          }
          const prev = neededVariants.get(variantId);
          const nextQty = (prev?.qty ?? 0) + qtyIn;
          neededVariants.set(variantId, { productId, qty: nextQty });
        } else {
          neededProducts.set(productId, (neededProducts.get(productId) ?? 0) + qtyIn);
        }
      }

      const insufficient: { product_id: number; variant_id: number | null; requested: number; available: number }[] = [];

      for (const [productId, needQty] of neededProducts.entries()) {
        const avail = productStockMap.get(productId) ?? 0;
        if (needQty > avail) {
          insufficient.push({ product_id: productId, variant_id: null, requested: needQty, available: avail });
        }
      }
      for (const [variantId, vNeed] of neededVariants.entries()) {
        const avail = variantMap.get(variantId)?.stock ?? 0;
        if (vNeed.qty > avail) {
          insufficient.push({ product_id: vNeed.productId, variant_id: variantId, requested: vNeed.qty, available: avail });
        }
      }

      if (insufficient.length) {
        throw new HttpError(409, "Stok tidak mencukupi.", { items: insufficient });
      }

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

      const updateProductStock = db!.prepare(
        `
        UPDATE products
        SET stock = stock - ?
        WHERE id = ? AND stock >= ?
      `
      );
      const updateVariantStock = db!.prepare(
        `
        UPDATE product_variants
        SET stock = stock - ?
        WHERE id = ? AND stock >= ?
      `
      );
      const insertMovement = hasMovements
        ? db!.prepare(
            `
            INSERT INTO product_stock_movements
              (product_id, delta, stock_before, stock_after, source, actor_user_id, actor_pelanggan_id, reference, note, created_at)
            VALUES
              (@product_id, @delta, @stock_before, @stock_after, @source, @actor_user_id, @actor_pelanggan_id, @reference, @note, datetime('now'))
          `
          )
        : null;

      const pelangganIdRaw = Number(order?.id_pelanggan ?? 0);
      const pelangganId = Number.isFinite(pelangganIdRaw) && pelangganIdRaw > 0 ? Math.trunc(pelangganIdRaw) : null;

      for (const [productId, qty] of neededProducts.entries()) {
        const before = productStockMap.get(productId) ?? 0;
        const res = updateProductStock.run(qty, productId, qty);
        if (Number(res?.changes ?? 0) <= 0) {
          throw new HttpError(409, "Stok tidak mencukupi.");
        }
        const after = Math.max(0, before - qty);
        insertMovement?.run({
          product_id: productId,
          delta: -qty,
          stock_before: before,
          stock_after: after,
          source: "ORDER_PAID",
          actor_user_id: null,
          actor_pelanggan_id: pelangganId,
          reference: orderId,
          note: "Pembayaran order",
        });
      }

      const touchedVariantProducts = new Set<number>();
      for (const [variantId, info] of neededVariants.entries()) {
        const before = variantMap.get(variantId)?.stock ?? 0;
        const res = updateVariantStock.run(info.qty, variantId, info.qty);
        if (Number(res?.changes ?? 0) <= 0) {
          throw new HttpError(409, "Stok tidak mencukupi.");
        }
        const after = Math.max(0, before - info.qty);
        insertMovement?.run({
          product_id: info.productId,
          delta: -info.qty,
          stock_before: before,
          stock_after: after,
          source: "ORDER_PAID",
          actor_user_id: null,
          actor_pelanggan_id: pelangganId,
          reference: orderId,
          note: "Pembayaran order (variant)",
        });
        touchedVariantProducts.add(info.productId);
      }

      const calcVariantSum = db!.prepare(
        `
        SELECT COALESCE(SUM(stock), 0) AS total_stock
        FROM product_variants
        WHERE product_id = ?
      `
      );
      const setProductStockExact = db!.prepare(
        `
        UPDATE products
        SET stock = ?
        WHERE id = ?
      `
      );

      for (const pid of touchedVariantProducts) {
        const row = calcVariantSum.get(pid) as { total_stock?: unknown } | undefined;
        const totalStock = Math.max(0, Math.trunc(Number(row?.total_stock ?? 0) || 0));
        setProductStockExact.run(totalStock, pid);
      }
    });

    payTx();

    return json(200, { success: true, order_id: orderId, redirect_url: `/invoice/${orderId}`, method });
  } catch (e) {
    const status = e instanceof HttpError ? e.status : 500;
    const message = e instanceof Error ? e.message : "Server error";
    const extra = e instanceof HttpError ? e.data : undefined;
    return json(status, { success: false, message, ...(extra ? { data: extra } : {}) });
  } finally {
    try {
      db?.close();
    } catch {}
  }
};
