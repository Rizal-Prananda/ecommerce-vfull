import type { APIRoute } from 'astro';

import { prisma } from '../../../lib/server/prisma';
import { requirePelangganAuth } from '../../../lib/server/auth';

type SendBody = {
	id_pelanggan?: unknown;
	pesan?: unknown;
};

function json(status: number, data: unknown) {
	return new Response(JSON.stringify(data), {
		status,
		headers: { 'Content-Type': 'application/json' }
	});
}

function isNonEmptyString(v: unknown): v is string {
	return typeof v === 'string' && v.trim().length > 0;
}

export const POST: APIRoute = async ({ request }) => {
	try {
		const auth = await requirePelangganAuth(request);
		if (!auth.ok) return json(auth.status, { ok: false, message: auth.message });

		const body = (await request.json().catch(() => null)) as SendBody | null;
		if (!body) return json(400, { ok: false, message: 'Body tidak valid' });

		const id = typeof body.id_pelanggan === 'number' ? body.id_pelanggan : Number(body.id_pelanggan);
		const pesan = isNonEmptyString(body.pesan) ? body.pesan.trim() : '';

		if (!Number.isInteger(id) || id <= 0) return json(400, { ok: false, message: 'id_pelanggan tidak valid' });
		if (auth.payload.id_pelanggan !== id) return json(403, { ok: false, message: 'Forbidden' });
		if (!pesan) return json(400, { ok: false, message: 'Pesan wajib diisi' });
		if (pesan.length > 2000) return json(400, { ok: false, message: 'Pesan terlalu panjang' });

		const created = await prisma.chat.create({
			data: {
				id_pelanggan: id,
				pengirim: 'pelanggan',
				pesan
			},
			select: {
				id_chat: true,
				id_pelanggan: true,
				pengirim: true,
				pesan: true,
				dibaca_admin: true,
				createdAt: true
			}
		});

		return json(201, { ok: true, data: created });
	} catch {
		return json(500, { ok: false, message: 'Server error' });
	}
};
