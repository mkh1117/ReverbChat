<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $dbThrottleKey = 'user_last_seen_db_updated_' . $user->id;

            if (!Cache::has($dbThrottleKey)) {

                $user->timestamps = false;
                User::where('id',$user->id)->update([
                'last_seen_at' => now(),
                ]);

                Cache::put($dbThrottleKey, true, 60);
            }
        }

        return $next($request);
    }
}
