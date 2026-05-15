<!doctype html>
<html lang="id" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-full bg-gray-50 text-gray-900">
    <main class="flex min-h-screen items-center justify-center px-6 py-12">
        <section class="w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 bg-white px-7 py-6">
                <div class="flex items-center gap-3">
                    <div class="grid h-10 w-10 place-items-center rounded-xl bg-slate-900 text-sm font-semibold text-white">R</div>
                    <div class="leading-tight">
                        <div class="text-sm font-semibold text-gray-900">Rizal CMS</div>
                        <div class="text-xs text-gray-500">Login Admin</div>
                    </div>
                </div>
            </div>

            <div class="px-7 py-6">
                <form method="POST" action="/login" class="space-y-4">
                    @csrf

                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-gray-900" for="email">Email</label>
                        <input id="email" name="email" type="email" required value="{{ old('email') }}"
                            class="h-11 w-full rounded-lg border border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-blue-500/20" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-gray-900" for="password">Password</label>
                        <input id="password" name="password" type="password" required
                            class="h-11 w-full rounded-lg border border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-blue-500/20" />
                    </div>

                    @if (session('error'))
                        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <div class="font-semibold">Form tidak valid.</div>
                        </div>
                    @endif

                    <button type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        Login
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>

</html>

