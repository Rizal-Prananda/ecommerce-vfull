import { useState } from 'react';
import toast from 'react-hot-toast';

export default function LoginForm() {
	const [email, setEmail] = useState('');
	const [password, setPassword] = useState('');
	const [loading, setLoading] = useState(false);

	const submit = async () => {
		setLoading(true);
		try {
			const res = await fetch('/api/login', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ email_pelanggan: email, password })
			});
			const json = await res.json().catch(() => null);
			if (!res.ok) throw new Error(json?.message ?? 'Login gagal');

			const token = String(json?.data?.token ?? '');
			const id = Number(json?.data?.id_pelanggan ?? 0);
			if (token && Number.isInteger(id) && id > 0) {
				window.localStorage.setItem('token_pelanggan', token);
				window.localStorage.setItem('id_pelanggan', String(id));
			}

			toast.success('Login berhasil');
			window.location.href = '/';
		} catch (e: unknown) {
			const msg = e instanceof Error ? e.message : 'Login gagal';
			toast.error(msg);
		} finally {
			setLoading(false);
		}
	};

	return (
		<form
			className="grid gap-4"
			onSubmit={(e) => {
				e.preventDefault();
				if (!loading) void submit();
			}}
		>
			<div className="grid gap-2">
				<label className="text-sm font-semibold text-slate-900">Email</label>
				<input
					type="email"
					className="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-blue-500"
					placeholder="nama@email.com"
					autoComplete="email"
					required
					value={email}
					onChange={(e) => setEmail(e.target.value)}
				/>
			</div>

			<div className="grid gap-2">
				<label className="text-sm font-semibold text-slate-900">Password</label>
				<input
					type="password"
					className="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-blue-500"
					placeholder="Password"
					autoComplete="current-password"
					required
					value={password}
					onChange={(e) => setPassword(e.target.value)}
				/>
			</div>

			<button
				type="submit"
				disabled={loading}
				className="mt-2 inline-flex h-11 items-center justify-center rounded-2xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
			>
				Login
			</button>
		</form>
	);
}
