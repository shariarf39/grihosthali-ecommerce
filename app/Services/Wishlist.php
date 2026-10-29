<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Wishlist: stored in the database for signed-in customers and in the session for guests.
 * A guest's list is merged into their account when they sign in or register.
 */
class Wishlist
{
    private const KEY = 'wishlist';

    /** @return int[] product ids */
    public function ids(): array
    {
        if ($user = auth()->user()) {
            return DB::table('wishlists')->where('user_id', $user->id)->latest()->pluck('product_id')->map(fn ($id) => (int) $id)->all();
        }

        return array_values(session(self::KEY, []));
    }

    public function count(): int
    {
        return count($this->ids());
    }

    /** Add or remove a product; returns true when it is now in the wishlist. */
    public function toggle(int $productId): bool
    {
        if ($user = auth()->user()) {
            $query = DB::table('wishlists')->where('user_id', $user->id)->where('product_id', $productId);
            if ($query->exists()) {
                $query->delete();

                return false;
            }
            DB::table('wishlists')->insert(['user_id' => $user->id, 'product_id' => $productId, 'created_at' => now(), 'updated_at' => now()]);

            return true;
        }

        $ids = session(self::KEY, []);
        if (in_array($productId, $ids, true)) {
            session([self::KEY => array_values(array_diff($ids, [$productId]))]);

            return false;
        }
        array_unshift($ids, $productId);
        session([self::KEY => $ids]);

        return true;
    }

    public function mergeGuestInto(User $user): void
    {
        foreach (session()->pull(self::KEY, []) as $productId) {
            DB::table('wishlists')->insertOrIgnore(['user_id' => $user->id, 'product_id' => $productId, 'created_at' => now(), 'updated_at' => now()]);
        }
    }
}
