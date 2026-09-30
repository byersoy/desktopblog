<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Message;

final class AdminMessageController
{
    public function index(Request $request): void
    {
        Auth::requireLogin();
        View::admin('admin/messages/index', [
            'messages' => Message::all(),
        ], 'messages', 'Mesajlar');
    }

    public function destroy(Request $request): void
    {
        Auth::requireLogin();
        if (!Csrf::verify($request->input('_csrf'))) {
            Response::redirect('/yonetim/mesajlar');
        }
        Message::delete((int) $request->param('id'));
        Session::flash('admin_success', 'Mesaj silindi.');
        Response::redirect('/yonetim/mesajlar');
    }
}
