<?php

namespace Modules\Currency\Http\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $lang = $request->user()?->preferences->lang ?? 'en';

        $info = json_decode($this->info);

        $name = $info->{$lang}?->name ?? $info->en?->name ?? null;

        return [
            'id' => $this->id,
            'symbol' => $this->symbol,
            'code' => $this->code,
            'name' => $name
        ];
    }
}
