<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Translation\{ArrayLoader, Translator};
use Illuminate\Validation\Factory;
use SimpleSoftwareIO\QrCode\Generator;

return function ($event) {
    $validationTranslations = require __DIR__ . '/vendor/illuminate/translation/lang/en/validation.php';
    $translations = (new ArrayLoader())->addMessages('en', 'validation', $validationTranslations);
    $validator = new Factory(new Translator($translations, 'en'));
    $validator
        ->make($event, [
            'correction' => 'required|string|in:L,M,Q,H',
            'format' => 'required|string|in:png,svg,eps',
            'size' => 'required|integer',
            'text' => 'required|string',
            'image' => 'url',
        ])
        ->validate();
    unset($validator);

    $qrGenerator = new Generator();
    $qrCode = $qrGenerator
        ->format($event['format'])
        ->errorCorrection($event['correction'])
        ->size($event['size']);
    unset($qrGenerator);

    if (isset($event['image'])) {
        $qrCode->merge($event['image'], .2, true);
    }

    return base64_encode($qrCode->generate($event['text'])->toHtml());
};
