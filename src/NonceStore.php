<?php

declare(strict_types=1);

namespace Foodieneers\Link;

final class NonceStore
{
    public function seen(): bool
    {
        // $this->cache()->has($this->cacheKey($key, $nonce, $ts));
        return false;
    }

    public function mark(): void
    {
        // $this->cache()->put($this->cacheKey($key, $nonce, $ts), 1, $ttlSeconds);
    }

    //     private function cacheKey(string $key, string $nonce, int $ts): string
    //     {
    //         return "Link:nonce:$key:$ts:$nonce";
    //     }

    //     private function cache()
    //     {
    //         $store = config('Link.cache_store');
    //         return $store ? Cache::store($store) : Cache::store();
    //     }
}
