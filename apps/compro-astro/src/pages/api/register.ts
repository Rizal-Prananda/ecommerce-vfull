import type { APIRoute } from 'astro';

import bcrypt from 'bcryptjs';
import { prisma } from '../../lib/server/prisma';

type RegisterBody = {
	namalengkap_pelanggan?: unknown;
	email_pelanggan?: unknown;
	notelepon_pelanggan?: unknown;
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
		const body = (await request.json().catch(() => null)) as RegisterBody | null;
		if (!body) return json(400, { ok: false, message: 'Body tidak valid' });

		const name = isNonEmptyString(body.namalengkap_pelanggan) ? body.namalengkap_pelanggan.trim() : '';
		const email = isNonEmptyString(body.email_pelanggan) ? body.email_pelanggan.trim().toLowerCase() : '';
		const phone = isNonEmptyString(body.notelepon_pelanggan) ? body.notelepon_pelanggan.trim() : null;
		const password = isNonEmptyString(body.password) ? body.password : '';

		if (!name) return json(400, { ok: false, message: 'Nama lengkap wajib diisi' });
		if (!email || !email.includes('@')) return json(400, { ok: false, message: 'Email tidak valid' });
		if (!password || password.length < 8) return json(400, { ok: false, message: 'Password minimal 8 karakter' });

		const hash = await bcrypt.hash(password, 10);

		const created = await prisma.pelanggan.create({
			data: {
				namalengkap_pelanggan: name,
				email_pelanggan: email,
				notelepon_pelanggan: phone,
				password: hash
			},
			select: {
				id_pelanggan: true,
				namalengkap_pelanggan: true,
				email_pelanggan: true,
				notelepon_pelanggan: true,
				createdAt: true
			}
		});

		return json(201, { ok: true, data: created });
	} catch (e: unknown) {
		const msg = e instanceof Error ? e.message : '';
		if (msg.includes('Unique constraint failed')) {
			return json(409, { ok: false, message: 'Email sudah terdaftar' });
		}
		return json(500, { ok: false, message: 'Server error' });
	}
};
