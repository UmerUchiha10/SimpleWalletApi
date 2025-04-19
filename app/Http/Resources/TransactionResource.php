<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        return [
            'sender'   => optional($this->sender)->name,
            'receiver' => optional($this->receiver)->name,
            'type' => $this->type,
            'amount'=> $this->amount,
        ];
    }
}
