<?php

declare(strict_types=1);

namespace Foodieneers\Bridge;

final class NonceStore
{
    public function seen(): bool
    {
        return false;
        // $this->cache()->has($this->cacheKey($key, $nonce, $ts));
    }

    public function mark(): void
    {
        // $this->cache()->put($this->cacheKey($key, $nonce, $ts), 1, $ttlSeconds);
    }

    //     private function cacheKey(string $key, string $nonce, int $ts): string
    //     {
    //         return "bridge:nonce:$key:$ts:$nonce";
    //     }

    //     private function cache()
    //     {
    //         $store = config('bridge.cache_store');
    //         return $store ? Cache::store($store) : Cache::store();
    //     }
}
