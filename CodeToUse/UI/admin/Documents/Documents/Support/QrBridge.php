<?php

namespace Modules\Documents\Support;
class QrBridge
{
    public static function qrCodeModel(): ?string
    {
        foreach (['Modules\QRTrack\Entities\QrCode', 'Modules\QRTrack\Entities\QrCode'] as $fqcn) {
            if (class_exists($fqcn)) return $fqcn;
        }
        return null;
    }

    public static function ensureCode(string $slug, array $meta = [])
    {
        $Model = self::qrCodeModel();
        if (!$Model) return null;

        $code = $Model::query()->where('slug', $slug)->first();
        if (!$code) {
            $code = new $Model();
            $code->tenant_id = function_exists('tenant') ? (tenant('id') ?? 0) : 0;
            $code->slug = $slug;
            $code->label = $meta['label'] ?? strtoupper($slug);
            $code->meta = $meta;
            $code->type = $meta['type'] ?? 'link';
            $code->status = $meta['status'] ?? 'active';
            $code->save();
        } else {
            $m = (array) ($code->meta ?? []);
            $code->meta = array_merge($m, $meta);
            $code->save();
        }
        return $code;
    }
}
