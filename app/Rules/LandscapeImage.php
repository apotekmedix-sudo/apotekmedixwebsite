<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class LandscapeImage implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile || !$value->isValid()) {
            $fail('File yang diunggah tidak valid.');
            return;
        }

        $imageInfo = getimagesize($value->getRealPath());
        if ($imageInfo === false) {
            $fail('File yang diunggah bukan gambar yang valid.');
            return;
        }

        [$width, $height] = $imageInfo;
        if ($width <= $height) {
            $fail('Foto harus dalam format landscape (lebar lebih besar dari tinggi).');
        }
    }

    public function passes(string $attribute, mixed $value): bool
    {
        $passed = true;
        $this->validate($attribute, $value, function () use (&$passed) {
            $passed = false;
        });

        return $passed;
    }
}
