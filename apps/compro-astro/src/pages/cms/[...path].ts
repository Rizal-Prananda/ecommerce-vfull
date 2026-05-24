import type { APIRoute } from 'astro';

export const prerender = false;

const RAW_CMS_URL = import.meta.env.CMS_INTERNAL_URL ?? import.meta.env.PUBLIC_CMS_URL ?? 'http://localhost:8001';

const resolveCmsBaseUrl = (requestUrl: string) => {
	const fallback = 'http://127.0.0.1:8001';
	const raw = String(RAW_CMS_URL || '').trim();
	if (!raw) return fallback;
	try {
		const req = new URL(requestUrl);
		const u = new URL(raw);
		const isLoopback = u.hostname === 'localhost' || u.hostname === '127.0.0.1' || u.hostname === '0.0.0.0';
		const sameAsRequest = u.origin === req.origin;
		if (sameAsRequest && !isLoopback) return fallback;
		return u.origin ? raw : fallback;
	} catch {
		return fallback;
	}
};

const pickHeader = (src: Headers, key: string) => {
	const v = src.get(key);
	return v === null ? undefined : v;
};

const handle = async (method: string, params: any, request: Request) => {
	const raw = String((params as any)?.path ?? '').trim();
	const path = raw.replace(/^\/+/, '');

	if (path === '' || path.includes('..')) {
		return new Response('Not found', { status: 404 });
	}

	const ok =
		path.startsWith('api/') ||
		path.startsWith('product-media/') ||
		path.startsWith('storage/') ||
		path.startsWith('uploads/');
	if (!ok) {
		return new Response('Not found', { status: 404 });
	}

	const fallbackBase = 'http://127.0.0.1:8001';
	const base = resolveCmsBaseUrl(request.url).replace(/\/$/, '');
	const reqUrl = new URL(request.url);
	let targetUrl = `${base}/${path}${reqUrl.search}`;

	let upstream: Response;
	try {
		const headers: Record<string, string> = {
			accept: request.headers.get('accept') ?? '*/*',
		};
		const contentType = request.headers.get('content-type');
		if (contentType) headers['content-type'] = contentType;
		const auth = request.headers.get('authorization');
		if (auth) headers['authorization'] = auth;
		const pelangganId = request.headers.get('x-pelanggan-id');
		if (pelangganId) headers['x-pelanggan-id'] = pelangganId;
		const range = request.headers.get('range');
		if (range) headers['range'] = range;

		const body = method === 'GET' || method === 'HEAD' ? undefined : await request.text();
		upstream = await fetch(targetUrl, {
			method,
			headers,
			body,
			redirect: 'follow',
		});
	} catch {
		return new Response('Bad gateway', { status: 502 });
	}

	if (upstream.status === 404 && base !== fallbackBase) {
		targetUrl = `${fallbackBase}/${path}${reqUrl.search}`;
		try {
			const headers: Record<string, string> = {
				accept: request.headers.get('accept') ?? '*/*',
			};
			const contentType = request.headers.get('content-type');
			if (contentType) headers['content-type'] = contentType;
			const auth = request.headers.get('authorization');
			if (auth) headers['authorization'] = auth;
			const pelangganId = request.headers.get('x-pelanggan-id');
			if (pelangganId) headers['x-pelanggan-id'] = pelangganId;
			const range = request.headers.get('range');
			if (range) headers['range'] = range;

			const body = method === 'GET' || method === 'HEAD' ? undefined : await request.text();
			upstream = await fetch(targetUrl, {
				method,
				headers,
				body,
				redirect: 'follow',
			});
		} catch {
			//
		}
	}

	const headers = new Headers();
	const contentType = pickHeader(upstream.headers, 'content-type');
	if (contentType) headers.set('content-type', contentType);

	const cacheControl = pickHeader(upstream.headers, 'cache-control');
	if (cacheControl) headers.set('cache-control', cacheControl);

	const etag = pickHeader(upstream.headers, 'etag');
	if (etag) headers.set('etag', etag);

	const lastModified = pickHeader(upstream.headers, 'last-modified');
	if (lastModified) headers.set('last-modified', lastModified);

	const acceptRanges = pickHeader(upstream.headers, 'accept-ranges');
	if (acceptRanges) headers.set('accept-ranges', acceptRanges);

	const contentRange = pickHeader(upstream.headers, 'content-range');
	if (contentRange) headers.set('content-range', contentRange);

	const contentLength = pickHeader(upstream.headers, 'content-length');
	if (contentLength) headers.set('content-length', contentLength);

	return new Response(upstream.body, {
		status: upstream.status,
		headers,
	});
};

export const GET: APIRoute = async ({ params, request }) => handle('GET', params, request);
export const POST: APIRoute = async ({ params, request }) => handle('POST', params, request);
export const PUT: APIRoute = async ({ params, request }) => handle('PUT', params, request);
export const PATCH: APIRoute = async ({ params, request }) => handle('PATCH', params, request);
export const DELETE: APIRoute = async ({ params, request }) => handle('DELETE', params, request);
