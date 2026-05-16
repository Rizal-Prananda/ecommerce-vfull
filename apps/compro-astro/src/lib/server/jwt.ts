import { SignJWT, jwtVerify } from 'jose';

export type PelangganJwtPayload = {
	id_pelanggan: number;
	email_pelanggan: string;
};

function getSecret() {
	const raw = import.meta.env.JWT_SECRET;
	if (!raw) throw new Error('JWT_SECRET belum di-set');
	return new TextEncoder().encode(String(raw));
}

export async function signPelangganToken(payload: PelangganJwtPayload) {
	const secret = getSecret();
	return new SignJWT(payload)
		.setProtectedHeader({ alg: 'HS256' })
		.setIssuedAt()
		.setExpirationTime('7d')
		.sign(secret);
}

export async function verifyPelangganToken(token: string) {
	const secret = getSecret();
	const { payload } = await jwtVerify(token, secret);
	const id = Number(payload.id_pelanggan);
	const email = String(payload.email_pelanggan ?? '');
	if (!Number.isInteger(id) || id <= 0) throw new Error('Token invalid');
	if (!email) throw new Error('Token invalid');
	return { id_pelanggan: id, email_pelanggan: email } satisfies PelangganJwtPayload;
}
