import { useState } from 'react';
import toast from 'react-hot-toast';

export default function RegisterForm() {
	const [name, setName] = useState('');
	const [email, setEmail] = useState('');
	const [phone, setPhone] = useState('');
	const [password, setPassword] = useState('');
	const [loading, setLoading] = useState(false);

	const submit = async () => {
		setLoading(true);
		try {
			const res = await fetch('/api/register', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					namalengkap_pelanggan: name,
					email_pelanggan: email,
					notelepon_pelanggan: phone || null,
					password
				})
			});
			const json = await res.json().catch(() => null);
			if (!res.ok) throw new Error(json?.message ?? 'Gagal daftar');

			const resLogin = await fetch('/api/login', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ email_pelanggan: email, password })
			});
			const jsonLogin = await resLogin.json().catch(() => null);
			if (!resLogin.ok) throw new Error(jsonLogin?.message ?? 'Daftar berhasil, tapi login gagal');

			const token = String(jsonLogin?.data?.token ?? '');
			const id = Number(jsonLogin?.data?.id_pelanggan ?? 0);
			if (token && Number.isInteger(id) && id > 0) {
				window.localStorage.setItem('token_pelanggan', token);
				window.localStorage.setItem('id_pelanggan', String(id));
			}

			toast.success('Daftar berhasil');
			window.location.href = '/';
		} catch (e: unknown) {
			const msg = e instanceof Error ? e.message : 'Gagal daftar';
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
				<label className="text-sm font-semibold text-slate-900">Nama Lengkap</label>
				<input
					className="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-blue-500"
					placeholder="Nama lengkap"
					autoComplete="name"
					required
					value={name}
					onChange={(e) => setName(e.target.value)}
				/>
			</div>

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
				<label className="text-sm font-semibold text-slate-900">No Telepon (opsional)</label>
				<input
					type="tel"
					className="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-blue-500"
					placeholder="081234567890"
					autoComplete="tel"
					value={phone}
					onChange={(e) => setPhone(e.target.value)}
				/>
			</div>

			<div className="grid gap-2">
				<label className="text-sm font-semibold text-slate-900">Password</label>
				<input
					type="password"
					minLength={8}
					className="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-blue-500"
					placeholder="Minimal 8 karakter"
					autoComplete="new-password"
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
				Daftar
			</button>
		</form>
	);
}
