<?php

namespace App\Services;

use App\Models\ServiceAsset;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class ServiceAssetQrCodeService
{
    public function urlFor(ServiceAsset $serviceAsset): string
    {
        return route('assets.qr.show', ['qrCode' => $serviceAsset->qr_code]);
    }

    public function svgFor(ServiceAsset $serviceAsset, int $size = 220): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd,
        );

        return (new Writer($renderer))->writeString($this->urlFor($serviceAsset));
    }
}
