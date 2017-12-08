<?php

namespace Spatie\MediaLibrary;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\MediaLibrary\Media;
use Illuminate\Contracts\Support\Responsable;
use ZipStream\ZipStream;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ZipStreamResponse implements Responsable
{
    /** string */
    protected $zipName;

    /** Illuminate\Support\Collection */
    protected $mediaItems;

    public static function create(string $zipName)
    {
        return new static($zipName);
    }

    public function __construct(string $zipName)
    {
        $this->zipName = $zipName;
    }

    public function addMedia($mediaItems)
    {
        $this->mediaItems = $mediaItems;

        return $this;
    }

    public function toResponse($request)
    {
        return new StreamedResponse(function () {
            $zip = new ZipStream($this->zipName, [
                'large_file_size' => 1
                ]);
            
            $this->mediaItems->each(function (Media $media) use ($zip) {
                $stream = $media->stream();

                $zip->addFileFromStream($media->file_name, $stream);

                fclose($stream);
            });
            
            $zip->finish();
        });
    }
}