<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChatAdminController extends Controller
{
    public function listConversations(): View
    {
        $conn = DB::connection('customer_sqlite');

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

        $conn = DB::connection('customer_sqlite');

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
            'pesan' => ['required', 'string', 'max:2000'],
        ]);

        $pesan = trim((string) $validated['pesan']);
        if ($pesan === '') {
            return back()->withErrors(['pesan' => 'Pesan wajib diisi.']);
        }

        $conn = DB::connection('customer_sqlite');
        $customer = $conn->table('Pelanggan')->where('id_pelanggan', $selectedId)->first();
        if (!$customer) {
            abort(404, 'Customer not found');
        }

        $conn->table('Chat')->insert([
            'id_pelanggan' => $selectedId,
            'pengirim' => 'admin',
            'pesan' => $pesan,
            'dibaca_admin' => 1,
            'createdAt' => now(),
        ]);

        return redirect("/dashboard/chat/{$selectedId}");
    }
}
