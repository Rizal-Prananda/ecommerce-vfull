import { useEffect, useMemo, useState } from 'react';

type NavItem = {
	label: string;
	href: string;
};

type NavbarMenuProps = {
	items: NavItem[];
	marketplaceHref?: string;
};

export default function NavbarMenu({ items, marketplaceHref }: NavbarMenuProps) {
	const [open, setOpen] = useState(false);

	const allItems = useMemo(() => {
		const base = [...items];
		if (marketplaceHref) base.push({ label: 'Marketplace', href: marketplaceHref });
		return base;
	}, [items, marketplaceHref]);

	useEffect(() => {
		if (!open) return;
		const onKeyDown = (e: KeyboardEvent) => {
			if (e.key === 'Escape') setOpen(false);
		};
		window.addEventListener('keydown', onKeyDown);
		return () => window.removeEventListener('keydown', onKeyDown);
	}, [open]);

	useEffect(() => {
		if (!open) return;
		const prevOverflow = document.body.style.overflow;
		document.body.style.overflow = 'hidden';
		return () => {
			document.body.style.overflow = prevOverflow;
		};
	}, [open]);

	useEffect(() => {
		const close = () => setOpen(false);
		window.addEventListener('hashchange', close);
		window.addEventListener('popstate', close);
		return () => {
			window.removeEventListener('hashchange', close);
			window.removeEventListener('popstate', close);
		};
	}, []);

	return (
		<div className="relative">
			<button
				type="button"
				className="inline-flex h-10 items-center justify-center rounded-2xl border border-gray-200 bg-white px-3 text-sm font-semibold text-black hover:bg-gray-50"
				aria-label="Buka menu"
				aria-expanded={open}
				aria-controls="navbar-mobile-panel"
				onClick={() => setOpen((v) => !v)}
			>
				Menu
			</button>

			<div
				className={[
					'fixed inset-0 z-[9999] md:hidden',
					open ? 'pointer-events-auto' : 'pointer-events-none'
				].join(' ')}
				aria-hidden={!open}
			>
				<button
					type="button"
					aria-label="Tutup menu"
					className={[
						'absolute inset-0 bg-slate-900/35 transition-opacity duration-200',
						open ? 'opacity-100' : 'opacity-0'
					].join(' ')}
					onClick={() => setOpen(false)}
					tabIndex={open ? 0 : -1}
				/>

				<div
					id="navbar-mobile-panel"
					className={[
						'absolute left-0 right-0 top-0 origin-top rounded-b-3xl border-b border-slate-200 bg-white/95 px-4 pb-5 pt-4 shadow-xl backdrop-blur',
						'transition-transform duration-200',
						open ? 'translate-y-0' : '-translate-y-6'
					].join(' ')}
					role="dialog"
					aria-modal="true"
				>
					<div className="flex items-center justify-between">
						<div className="text-sm font-semibold text-slate-900">Menu</div>
						<button
							type="button"
							className="inline-flex h-10 items-center justify-center rounded-2xl border border-gray-200 bg-white px-3 text-sm font-semibold text-black hover:bg-gray-50"
							onClick={() => setOpen(false)}
						>
							Tutup
						</button>
					</div>

					<nav className="mt-4 grid gap-1 text-sm font-medium text-slate-700" aria-label="Mobile">
						{allItems.map((item) => (
							<a
								key={item.href}
								href={item.href}
								className="rounded-2xl px-3 py-2 hover:bg-slate-50"
								onClick={() => setOpen(false)}
							>
								{item.label}
							</a>
						))}
					</nav>

					<div className="mt-4 grid grid-cols-2 gap-2">
						<a
							href="/login"
							className="inline-flex h-11 items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 text-sm font-semibold text-black hover:bg-gray-50"
							onClick={() => setOpen(false)}
						>
							Login
						</a>
						<a
							href="/register"
							className="inline-flex h-11 items-center justify-center rounded-2xl bg-black px-4 text-sm font-semibold text-white hover:bg-gray-900"
							onClick={() => setOpen(false)}
						>
							Daftar
						</a>
					</div>
				</div>
			</div>
		</div>
	);
}
