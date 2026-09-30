<?php

declare(strict_types=1);

namespace App\Core;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;

final class Markdown
{
    private static ?MarkdownConverter $converter = null;

    private static function converter(): MarkdownConverter
    {
        if (self::$converter !== null) {
            return self::$converter;
        }

        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'insert' => 'after',
            ],
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new HeadingPermalinkExtension());

        self::$converter = new MarkdownConverter($environment);
        return self::$converter;
    }

    public static function toHtml(string $markdown): string
    {
        return (string) self::converter()->convert($markdown);
    }

    public static function toPlainText(string $markdown): string
    {
        return trim(strip_tags(self::toHtml($markdown)));
    }
}
