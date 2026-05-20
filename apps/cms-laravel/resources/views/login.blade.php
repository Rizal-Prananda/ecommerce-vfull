<!doctype html>
<html lang="id" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white text-slate-900">
    <main class="min-h-screen">
        <div class="flex min-h-screen">
            <div class="relative hidden overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 lg:flex lg:w-1/2">
                <div class="absolute inset-0 opacity-40"
                    style="background-image: linear-gradient(to right, rgba(79,70,229,0.08) 1px, transparent 1px), linear-gradient(to bottom, rgba(79,70,229,0.08) 1px, transparent 1px); background-size: 60px 60px;">
                </div>
                <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-blue-600/20 blur-3xl"></div>
                <div class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-purple-600/10 blur-3xl"></div>

                <div class="relative z-10 flex w-full flex-col justify-between p-12">
                    
                    <div class="cms-sidebar-header flex h-auto flex-col items-center justify-center ">
                        <img src="{{ asset('logo5.png') }}" alt="Avenue Collective" class="size-13 rounded-xl object-cover " onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';"> 
                        <div x-show="sidebarOpen" x-transition.opacity.duration.300ms class="leading-tight text-center" x-cloak>
                            <div class="text-xl font-semibold text-white">Avenue Collective</div>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500">© {{ date('Y') }} CMS - E-Commerce</p>
                </div>
            </div>

            <div class="flex w-full items-center justify-center px-6 py-12 lg:w-1/2">
                <div class="w-full max-w-sm">
                    <div class="mb-8 lg:hidden">
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900">
                            <span class="text-lg font-bold text-white">R</span>
                        </div>
                    </div>

                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Akses Eksklusif</h1>
                    <p class="mt-2 text-sm text-slate-600">Masuk untuk melanjutkan pengalaman eksklusif</p>

                    <form method="POST" action="{{ route('login') }}" class="mt-8">
                        @csrf

                        <div class="space-y-5 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xl shadow-slate-900/5">
                            <div>
                                <label for="login" class="block text-sm font-medium text-slate-700">Email atau Username</label>
                                <div class="relative mt-1.5">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                        </svg>
                                    </div>
                                    <input
                                        type="text"
                                        name="login"
                                        id="login"
                                        required
                                        autofocus
                                        value="{{ old('login') }}"
                                        placeholder="email@example.com atau username"
                                        class="block w-full rounded-lg border border-slate-300 bg-slate-50/50 py-2.5 pl-9 pr-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                    />
                                </div>
                                @error('login')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <div class="flex items-center justify-between">
                                    <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi</label>
                                    @if (\Illuminate\Support\Facades\Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-xs font-medium text-blue-600 hover:text-blue-500">Forgot?</a>
                                    @endif
                                </div>
                                <div class="relative mt-1.5">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        required
                                        placeholder="••••••••"
                                        class="block w-full rounded-lg border border-slate-300 bg-slate-50/50 py-2.5 pl-9 pr-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                    />
                                </div>
                                @error('password')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="flex items-center justify-between pl-4">
                                <div class="flex items-center">
                                    <input type="checkbox" name="showpassword" id="showpassword" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" {{ old('showpassword') ? 'checked' : '' }}>
                                    <label for="showpassword" class="ml-2 text-sm text-slate-700">Perlihatkan Kata Sandi</label>
                                </div>
                            </div>

                            @if (session('error'))
                                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <button type="submit"
                                class="group relative flex w-full justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-slate-800 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-slate-900/20 active:scale-[0.98]">
                                <span class="relative z-10">Log In</span>
                                <div class="absolute inset-0 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 opacity-0 transition-opacity group-hover:opacity-100"></div>
                            </button>
                        </div>
                    </form>

                    <p class="mt-6 text-center text-xs text-slate-500">
                        Dengan login, kamu menyetujui <a href="#" class="font-medium text-slate-700 hover:underline">kebijakan</a> internal.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <script>
        const showPasswordCheckbox = document.getElementById('showpassword');
        const passwordInput = document.getElementById('password');

        showPasswordCheckbox.addEventListener('change', function() {
            if (this.checked) {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        });
    </script>
</body>

</html>
