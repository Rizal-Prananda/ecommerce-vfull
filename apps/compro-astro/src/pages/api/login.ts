import type { APIRoute } from 'astro';

import bcrypt from 'bcryptjs';
import { prisma } from '../../lib/server/prisma';
import { signPelangganToken } from '../../lib/server/jwt';

type LoginBody = {
	email_pelanggan?: unknown;
	password?: unknown;
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
		const body = (await request.json().catch(() => null)) as LoginBody | null;
		if (!body) return json(400, { ok: false, message: 'Body tidak valid' });

		const email = isNonEmptyString(body.email_pelanggan) ? body.email_pelanggan.trim().toLowerCase() : '';
		const password = isNonEmptyString(body.password) ? body.password : '';

		if (!email || !email.includes('@')) return json(400, { ok: false, message: 'Email tidak valid' });
		if (!password) return json(400, { ok: false, message: 'Password wajib diisi' });

		const pelanggan = await prisma.pelanggan.findUnique({
			where: { email_pelanggan: email },
			select: {
				id_pelanggan: true,
				email_pelanggan: true,
				password: true
			}
		});

		if (!pelanggan) return json(401, { ok: false, message: 'Email atau password salah' });
		const ok = await bcrypt.compare(password, pelanggan.password);
		if (!ok) return json(401, { ok: false, message: 'Email atau password salah' });

		const token = await signPelangganToken({
			id_pelanggan: pelanggan.id_pelanggan,
			email_pelanggan: pelanggan.email_pelanggan
		});

		return json(200, {
			ok: true,
			data: { token, id_pelanggan: pelanggan.id_pelanggan, email_pelanggan: pelanggan.email_pelanggan }
		});
	} catch {
		return json(500, { ok: false, message: 'Server error' });
	}
};
