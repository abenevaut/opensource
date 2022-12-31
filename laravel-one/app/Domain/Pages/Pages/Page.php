<?php

namespace App\Domain\Pages\Pages;

use Spatie\LaravelData\Data;

class Page extends Data //implements \SplSubject
{
//    private array $observers = [];

    public function __construct(
        public string $file,
        public string $distUrl,
        public string $distUri,
//        public array $content
    ) {
    }

//    public function attach(\SplObserver $observer)
//    {
//        $this->observers[] = $observer;
//    }
//
//    public function detach(\SplObserver $observer)
//    {
//        $key = array_search($observer, $this->observers, true);
//
//        if ($key) {
//            unset($this->observers[$key]);
//        }
//    }
//
//    public function notify()
//    {
//        foreach ($this->observers as $observer) {
//            $observer->update($this);
//        }
//    }
}
