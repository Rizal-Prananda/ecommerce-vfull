import type { APIRoute } from 'astro';

import { prisma } from '../../../lib/server/prisma';
import { requirePelangganAuth } from '../../../lib/server/auth';

function json(status: number, data: unknown) {
	return new Response(JSON.stringify(data), {
		status,
		headers: { 'Content-Type': 'application/json' }
	});
}

export const GET: APIRoute = async ({ request }) => {
	try {
		const auth = await requirePelangganAuth(request);
		if (!auth.ok) return json(auth.status, { ok: false, message: auth.message });

		const url = new URL(request.url);
		const id = Number(url.searchParams.get('id_pelanggan'));
		if (!Number.isInteger(id) || id <= 0) return json(400, { ok: false, message: 'id_pelanggan tidak valid' });
		if (auth.payload.id_pelanggan !== id) return json(403, { ok: false, message: 'Forbidden' });

		const items = await prisma.chat.findMany({
			where: { id_pelanggan: id },
			orderBy: { createdAt: 'asc' },
			select: {
				id_chat: true,
				id_pelanggan: true,
				pengirim: true,
				pesan: true,
				dibaca_admin: true,
				createdAt: true
			}
		});

		return json(200, { ok: true, data: items });
	} catch {
		return json(500, { ok: false, message: 'Server error' });
	}
};
