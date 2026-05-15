import { cookies } from "next/headers";

import { verifySession, COOKIE_NAME } from "@/lib/auth";
import { prisma } from "@/lib/prisma";

async function requireUserId() {
  const jar = await cookies();
  const token = jar.get(COOKIE_NAME)?.value;
  if (!token) return null;
  try {
    const payload = await verifySession(token);
    const userId = Number(payload?.sub);
    return Number.isFinite(userId) ? userId : null;
  } catch {
    return null;
  }
}

export async function GET() {
  const userId = await requireUserId();
  if (!userId) {
    return Response.json({ error: "Unauthorized" }, { status: 401 });
  }

  const orders = await prisma.order.findMany({
    where: { userId },
    orderBy: { createdAt: "desc" },
  });
  return Response.json({ data: orders });
}

export async function POST(request) {
  const userId = await requireUserId();
  if (!userId) {
    return Response.json({ error: "Unauthorized" }, { status: 401 });
  }

  const body = await request.json().catch(() => null);
  const total = Number(body?.total ?? 0);
  const items = body?.items ?? [];

  if (!Number.isFinite(total) || total <= 0) {
    return Response.json({ error: "Total tidak valid." }, { status: 400 });
  }

  const order = await prisma.order.create({
    data: {
      userId,
      total: Math.round(total),
      items,
      status: "pending",
    },
  });

  return Response.json({ data: order });
}

