<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChatAdminController extends Controller
{
    public function listConversations(): View
    {
        $conn = DB::connection('sqlite');

        $customers = $conn
            ->table('Pelanggan as p')
            ->leftJoin('Chat as c', 'c.id_pelanggan', '=', 'p.id_pelanggan')
            ->select(
                'p.id_pelanggan',
                'p.namalengkap_pelanggan',
                'p.email_pelanggan',
                'p.notelepon_pelanggan'
            )
            ->selectRaw('MAX(c.createdAt) as last_message_at')
            ->selectRaw("SUM(CASE WHEN c.pengirim = 'pelanggan' AND c.dibaca_admin = 0 THEN 1 ELSE 0 END) as unread_count")
            ->groupBy('p.id_pelanggan', 'p.namalengkap_pelanggan', 'p.email_pelanggan', 'p.notelepon_pelanggan')
            ->orderByRaw('CASE WHEN last_message_at IS NULL THEN 1 ELSE 0 END, last_message_at DESC')
            ->get();

        $selectedId = (int) ($customers->first()->id_pelanggan ?? 0);
        $selectedCustomer = null;
        $messages = collect();

        if ($selectedId > 0) {
            $selectedCustomer = $conn->table('Pelanggan')->where('id_pelanggan', $selectedId)->first();
            $messages = $conn->table('Chat')->where('id_pelanggan', $selectedId)->orderBy('createdAt', 'asc')->get();

            $conn
                ->table('Chat')
                ->where('id_pelanggan', $selectedId)
                ->where('pengirim', 'pelanggan')
                ->where('dibaca_admin', 0)
                ->update(['dibaca_admin' => 1]);
        }

        return view('chat-messages', [
            'customers' => $customers,
            'selectedId' => $selectedId,
            'selectedCustomer' => $selectedCustomer,
            'messages' => $messages,
        ]);
    }

    public function viewConversation($conversationId): View
    {
        $selectedId = (int) $conversationId;
        if ($selectedId <= 0) {
            abort(404, 'Customer not found');
        }

        $conn = DB::connection('sqlite');

        $customers = $conn
            ->table('Pelanggan as p')
            ->leftJoin('Chat as c', 'c.id_pelanggan', '=', 'p.id_pelanggan')
            ->select(
                'p.id_pelanggan',
                'p.namalengkap_pelanggan',
                'p.email_pelanggan',
                'p.notelepon_pelanggan'
            )
            ->selectRaw('MAX(c.createdAt) as last_message_at')
            ->selectRaw("SUM(CASE WHEN c.pengirim = 'pelanggan' AND c.dibaca_admin = 0 THEN 1 ELSE 0 END) as unread_count")
            ->groupBy('p.id_pelanggan', 'p.namalengkap_pelanggan', 'p.email_pelanggan', 'p.notelepon_pelanggan')
            ->orderByRaw('CASE WHEN last_message_at IS NULL THEN 1 ELSE 0 END, last_message_at DESC')
            ->get();

        $selectedCustomer = $conn->table('Pelanggan')->where('id_pelanggan', $selectedId)->first();
        if (!$selectedCustomer) {
            abort(404, 'Customer not found');
        }

        $messages = $conn->table('Chat')->where('id_pelanggan', $selectedId)->orderBy('createdAt', 'asc')->get();

        $conn
            ->table('Chat')
            ->where('id_pelanggan', $selectedId)
            ->where('pengirim', 'pelanggan')
            ->where('dibaca_admin', 0)
            ->update(['dibaca_admin' => 1]);

        return view('chat-messages', [
            'customers' => $customers,
            'selectedId' => $selectedId,
            'selectedCustomer' => $selectedCustomer,
            'messages' => $messages,
        ]);
    }

    public function reply(Request $request, $conversationId): RedirectResponse
    {
        $selectedId = (int) $conversationId;
        if ($selectedId <= 0) {
            abort(404, 'Customer not found');
        }

        $validated = $request->validate([
            'pesan' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp,gif,pdf'],
        ]);

        $pesan = trim((string) ($validated['pesan'] ?? ''));

        $conn = DB::connection('sqlite');
        $customer = $conn->table('Pelanggan')->where('id_pelanggan', $selectedId)->first();
        if (!$customer) {
            abort(404, 'Customer not found');
        }

        $file = $request->file('attachment');
        $attachmentPath = null;
        $attachmentOriginalName = null;
        $attachmentMime = null;
        $attachmentSize = null;

        if ($file) {
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $filename = (string) Str::uuid() . ($ext !== '' ? ".{$ext}" : '');
            $attachmentPath = $file->storeAs('chat-attachments', $filename, 'public');
            $attachmentOriginalName = (string) $file->getClientOriginalName();
            $attachmentMime = (string) ($file->getClientMimeType() ?? '');
            $attachmentSize = (int) ($file->getSize() ?? 0);
        }

        if ($pesan === '' && !$file) {
            return back()->withErrors([
                'pesan' => 'Isi pesan atau pilih lampiran.',
            ]);
        }

        $admin = $request->user();
        $adminId = (int) ($admin?->id ?? 0);
        $adminName = trim((string) ($admin?->name ?? 'Admin'));

        $createdAt = now();
        $chatId = $conn->table('Chat')->insertGetId([
            'id_pelanggan' => $selectedId,
            'pengirim' => 'admin',
            'pengirim_user_id' => $adminId > 0 ? $adminId : null,
            'pengirim_nama' => $adminName !== '' ? $adminName : null,
            'pesan' => $pesan,
            'dibaca_admin' => 1,
            'createdAt' => $createdAt,
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => $attachmentOriginalName,
            'attachment_mime' => $attachmentMime,
            'attachment_size' => $attachmentSize,
        ], 'id_chat');

        if ($request->wantsJson() || $request->expectsJson()) {
            $attachmentUrl = null;
            if (is_string($attachmentPath) && $attachmentPath !== '') {
                $attachmentUrl = Storage::disk('public')->url($attachmentPath);
            }

            return response()->json([
                'ok' => true,
                'data' => [
                    'id_chat' => (int) $chatId,
                    'id_pelanggan' => $selectedId,
                    'pengirim' => 'admin',
                    'pengirim_nama' => $adminName !== '' ? $adminName : 'Admin',
                    'pesan' => $pesan,
                    'createdAt' => $createdAt->toISOString(),
                    'attachment_url' => $attachmentUrl,
                    'attachment_original_name' => $attachmentOriginalName,
                    'attachment_mime' => $attachmentMime,
                ],
            ]);
        }

        return redirect("/dashboard/chat/{$selectedId}");
    }
}
