<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Markdown;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Str;
use App\Core\Uploader;
use App\Core\Validator;
use App\Core\View;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;

final class AdminPostController
{
    public function index(Request $request): void
    {
        Auth::requireLogin();
        View::admin('admin/posts/index', [
            'posts' => Post::allForAdmin(),
        ], 'posts', 'Yazılar');
    }

    public function create(Request $request): void
    {
        Auth::requireLogin();
        View::admin('admin/posts/form', [
            'post' => null,
            'tagsSelected' => '',
            'categories' => Category::all(),
            'allTags' => Tag::all(),
            'errors' => [],
        ], 'posts', 'Yeni Yazı');
    }

    public function store(Request $request): void
    {
        Auth::requireLogin();
        $this->save($request, null);
    }

    public function edit(Request $request): void
    {
        Auth::requireLogin();
        $id = (int) $request->param('id');
        $post = Post::find($id);
        if ($post === null) {
            Response::redirect('/yonetim/yazilar');
        }

        $tags = Post::tagsFor($id);

        View::admin('admin/posts/form', [
            'post' => $post,
            'tagsSelected' => implode(', ', array_column($tags, 'name')),
            'categories' => Category::all(),
            'allTags' => Tag::all(),
            'errors' => [],
        ], 'posts', 'Yazıyı Düzenle');
    }

    public function update(Request $request): void
    {
        Auth::requireLogin();
        $id = (int) $request->param('id');
        $this->save($request, $id);
    }

    private function save(Request $request, ?int $id): void
    {
        if (!Csrf::verify($request->input('_csrf'))) {
            Response::redirect('/yonetim/yazilar');
        }

        $title = trim((string) $request->input('title', ''));
        $slugInput = trim((string) $request->input('slug', ''));
        $slug = $slugInput !== '' ? Str::slug($slugInput) : Str::slug($title);
        $excerpt = trim((string) $request->input('excerpt', ''));
        $contentMd = (string) $request->input('content_md', '');
        $status = $request->input('status') === 'published' ? 'published' : 'draft';
        $categoryId = $request->input('category_id') !== '' ? (int) $request->input('category_id') : null;
        $metaTitle = trim((string) $request->input('meta_title', '')) ?: null;
        $metaDescription = trim((string) $request->input('meta_description', '')) ?: null;
        $coverImage = trim((string) $request->input('cover_image', '')) ?: null;
        $publishedAtInput = trim((string) $request->input('published_at', ''));
        $tagNames = array_filter(array_map('trim', explode(',', (string) $request->input('tags', ''))));

        $validator = new Validator(['title' => $title, 'content_md' => $contentMd]);
        $validator->required('title', 'Başlık')->required('content_md', 'İçerik');

        if ($slug === '') {
            $slug = Str::random(8);
        }
        if (Post::slugExists($slug, $id)) {
            $slug .= '-' . Str::random(4);
        }

        if ($validator->fails()) {
            $resubmitted = [
                'id' => $id,
                'title' => $title,
                'slug' => $slugInput,
                'excerpt' => $excerpt,
                'content_md' => $contentMd,
                'cover_image' => $coverImage,
                'status' => $status,
                'category_id' => $categoryId,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'published_at' => $publishedAtInput,
            ];
            View::admin('admin/posts/form', [
                'post' => $resubmitted,
                'tagsSelected' => implode(', ', $tagNames),
                'categories' => Category::all(),
                'allTags' => Tag::all(),
                'errors' => $validator->errors(),
            ], 'posts', $id ? 'Yazıyı Düzenle' : 'Yeni Yazı');
            return;
        }

        $contentHtml = Markdown::toHtml($contentMd);
        $plainText = Markdown::toPlainText($contentMd);
        $finalExcerpt = $excerpt !== '' ? $excerpt : Str::excerpt($plainText);
        $readingTime = Str::readingTimeMinutes($plainText);
        $publishedAt = $publishedAtInput !== '' ? date('Y-m-d H:i:s', strtotime($publishedAtInput)) : ($status === 'published' ? date('Y-m-d H:i:s') : null);

        $data = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $finalExcerpt,
            'content_md' => $contentMd,
            'content_html' => $contentHtml,
            'cover_image' => $coverImage,
            'status' => $status,
            'category_id' => $categoryId,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'published_at' => $publishedAt,
            'reading_time' => $readingTime,
        ];

        if ($id === null) {
            $data['author_id'] = Auth::id();
            $postId = Post::create($data);
        } else {
            Post::update($id, $data);
            $postId = $id;
        }

        $tagIds = Tag::findOrCreateByNames($tagNames);
        Post::syncTags($postId, $tagIds);

        Session::flash('admin_success', $id === null ? 'Yazı oluşturuldu.' : 'Yazı güncellendi.');
        Response::redirect('/yonetim/yazilar/' . $postId . '/duzenle');
    }

    public function destroy(Request $request): void
    {
        Auth::requireLogin();
        if (!Csrf::verify($request->input('_csrf'))) {
            Response::redirect('/yonetim/yazilar');
        }
        Post::delete((int) $request->param('id'));
        Session::flash('admin_success', 'Yazı silindi.');
        Response::redirect('/yonetim/yazilar');
    }

    public function uploadImage(Request $request): void
    {
        Auth::requireLogin();
        if (!Csrf::verify($request->input('_csrf'))) {
            Response::json(['error' => 'Geçersiz istek.'], 419);
        }

        $file = $request->file('image');
        if ($file === null) {
            Response::json(['error' => 'Dosya bulunamadı.'], 422);
        }

        try {
            $url = Uploader::storePostImage($file);
            Response::json(['url' => $url]);
        } catch (\RuntimeException $e) {
            Response::json(['error' => $e->getMessage()], 422);
        }
    }

    public function preview(Request $request): void
    {
        Auth::requireLogin();
        if (!Csrf::verify($request->input('_csrf'))) {
            Response::json(['error' => 'Geçersiz istek.'], 419);
        }
        $md = (string) $request->input('content_md', '');
        Response::json(['html' => Markdown::toHtml($md)]);
    }
}
