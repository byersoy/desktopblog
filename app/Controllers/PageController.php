<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Seo;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\Message;

final class PageController
{
    public function about(Request $request): void
    {
        View::render('pages/about', [], 'layouts/desktop', [
            'id' => 'hakkimda',
            'title' => 'Hakkımda',
            'kind' => 'about',
        ], [
            'seo' => [
                'title' => Seo::title('Hakkımda'),
                'description' => App::setting('about_summary', 'Bu blog ve yazarı hakkında bilgi.'),
                'canonical' => Seo::siteUrl('/hakkimda'),
            ],
        ]);
    }

    public function contact(Request $request): void
    {
        $rawErrors = Session::flash('contact_errors');
        View::render('pages/contact', [
            'errors' => $rawErrors ? json_decode($rawErrors, true) : [],
            'success' => Session::flash('contact_success'),
            'old' => $rawErrors ? (json_decode((string) Session::flash('contact_old'), true) ?: []) : [],
        ], 'layouts/desktop', [
            'id' => 'iletisim',
            'title' => 'İletişim',
            'kind' => 'mail',
        ], [
            'seo' => [
                'title' => Seo::title('İletişim'),
                'description' => 'Benimle iletişime geçin.',
                'canonical' => Seo::siteUrl('/iletisim'),
            ],
        ]);
    }

    public function contactSubmit(Request $request): void
    {
        if (!Csrf::verify($request->input('_csrf'))) {
            Response::redirect('/iletisim');
        }

        // Honeypot: gercek kullanicilar bu alani gormez/doldurmaz, botlar doldurur.
        if (trim((string) $request->input('website', '')) !== '') {
            Session::flash('contact_success', 'Mesajınız alındı, teşekkürler!');
            Response::redirect('/iletisim');
        }

        $data = [
            'name' => $request->input('name', ''),
            'email' => $request->input('email', ''),
            'body' => $request->input('body', ''),
        ];

        $validator = new Validator($data);
        $validator->required('name', 'Ad Soyad')
            ->required('email', 'E-posta')->email('email', 'E-posta')
            ->required('body', 'Mesaj')->minLength('body', 10, 'Mesaj');

        if ($validator->fails()) {
            Session::flash('contact_errors', json_encode($validator->errors(), JSON_UNESCAPED_UNICODE));
            Session::flash('contact_old', json_encode($data, JSON_UNESCAPED_UNICODE));
            Response::redirect('/iletisim');
        }

        Message::create($data['name'], $data['email'], $data['body'], $request->ip());
        Session::flash('contact_success', 'Mesajınız alındı, teşekkürler!');
        Response::redirect('/iletisim');
    }
}
