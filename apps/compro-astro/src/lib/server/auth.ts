import { verifyPelangganToken } from './jwt';

export async function requirePelangganAuth(request: Request) {
	const header = request.headers.get('authorization') ?? request.headers.get('Authorization') ?? '';
	const m = header.match(/^Bearer\s+(.+)$/i);
	const token = (m?.[1] ?? '').trim();
	const devIdHeader = (request.headers.get('x-pelanggan-id') ?? request.headers.get('X-Pelanggan-Id') ?? '').trim();
	const devId = Number(devIdHeader);
	const canUseDevAuth = import.meta.env.DEV && Number.isInteger(devId) && devId > 0;

	if (!token) {
		if (canUseDevAuth) {
			return { ok: true as const, status: 200, payload: { id_pelanggan: devId, email_pelanggan: 'dev@local' } };
		}
		return { ok: false as const, status: 401, message: 'Unauthorized' };
	}

	try {
		const payload = await verifyPelangganToken(token);
		return { ok: true as const, status: 200, payload };
	} catch {
		if (canUseDevAuth) {
			return { ok: true as const, status: 200, payload: { id_pelanggan: devId, email_pelanggan: 'dev@local' } };
		}
		return { ok: false as const, status: 401, message: 'Unauthorized' };
	}
}
