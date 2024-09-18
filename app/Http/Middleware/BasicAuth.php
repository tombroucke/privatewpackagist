<?php

namespace App\Http\Middleware;

use App\Models\Token;
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
        if ($request->user()) {
            return $next($request);
        }

        $username = $request->getUser();
        $password = $request->getPassword();

        $token = Token::where('username', $username)
            ->where('token', $password)->first();

        if (! $token) {
            return response('Unauthorized.', 401, ['WWW-Authenticate' => 'Basic']);
        } elseif ($token->deactivated_at) {
            $token->activity()->create([
                'action' => 'authenticate_blocked',
                'message' => 'Token has been used to authenticate but is deactivated.',
                'ip_address' => $request->ip(),
                'user_id' => null,
            ]);

            return response('Unauthorized.', 401, ['WWW-Authenticate' => 'Basic']);
        }

        $token->activity()->create([
            'action' => 'authenticate',
            'message' => 'Token has been used to authenticate.',
            'ip_address' => $request->ip(),
            'user_id' => null,
        ]);

        return $next($request);
    }
}
