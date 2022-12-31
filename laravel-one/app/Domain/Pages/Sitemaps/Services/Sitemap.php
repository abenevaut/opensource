<?php

namespace App\Domain\Pages\Sitemaps\Services;

use App\Domain\Pages\Pages\Page;
use Spatie\Sitemap\Sitemap as SpatieSitemap;
use Spatie\Sitemap\Tags\Url;

class Sitemap extends SpatieSitemap implements \SplObserver
{
    public function update(Page|\SplSubject $subject)
    {
        if (
            !array_key_exists('sitemap', $subject->content)
            || $subject->content['sitemap'] === true
        ) {
            $this->add(
                URL::create("{$subject->distUrl}/{$subject->distUri}")
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.1)
            );
        }
    }

    public function generate()
    {
        $this->writeToFile(getcwd() . DIRECTORY_SEPARATOR . 'dist/sitemap.xml');
    }

    public function render(): string
    {
        $tags = collect($this->tags)
            ->unique('url')
            ->filter();

        return view('sitemap')
            ->with(compact('tags'))
            ->render();
    }
}
