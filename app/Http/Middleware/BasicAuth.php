<?php

namespace App\Http\Middleware;

use App\Models\Token;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $username = $request->getUser();
        $password = $request->getPassword();

        // find token where username=username en token=password
        $token = Token::where('username', $username)->where('token', $password)->first();

        // Validate request headers against the provided credentials
        if (! $token) {

            return response('Unauthorized.', 401, ['WWW-Authenticate' => 'Basic']);
        }

        $token->last_used_at = Carbon::now();
        $token->save();

        return $next($request);
    }
}
