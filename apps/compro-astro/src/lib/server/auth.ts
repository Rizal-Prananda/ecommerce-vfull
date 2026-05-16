import { verifyPelangganToken } from './jwt';

export async function requirePelangganAuth(request: Request) {
	const header = request.headers.get('authorization') ?? request.headers.get('Authorization') ?? '';
	const m = header.match(/^Bearer\s+(.+)$/i);
	const token = (m?.[1] ?? '').trim();
	if (!token) return { ok: false as const, status: 401, message: 'Unauthorized' };

	try {
		const payload = await verifyPelangganToken(token);
		return { ok: true as const, status: 200, payload };
	} catch {
		return { ok: false as const, status: 401, message: 'Unauthorized' };
	}
}
