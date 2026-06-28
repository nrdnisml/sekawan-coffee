<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductThumbnailController
{
    public function __invoke(string $path): Response
    {
        abort_unless(str_starts_with($path, 'products/'), 404);

        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $contentType = match ($extension) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'application/octet-stream',
        };

        return response($disk->get($path), 200, [
            'Content-Type' => $contentType,
        ]);
    }
}
