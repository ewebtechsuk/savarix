<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use App\Models\TokenVerfier;
use App\Models\Company;
use App\Models\Expert;

class Token
{
    /**
     * The authentication guard instance.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header( 'Token' );

        if ( empty( $token ) )
        {
            if( $request->has('token') )
                $token = $request->input('token');
            else
                return response()->json ( [ 'status'=>'error', 'error'=>['message' => 'Please Login to continue!' ]] , 403 );
        }

        $token = TokenVerfier::where ( 'token' , '=' , $token )->first ();
        if ( ! $token )
        {
            return response()->json ( [ 'status'=>'error', 'error'=>['message' => 'token is missing' ]] , 403 );
        }
        if( $token->type == 'admin' )
        {
            $user = Company::find($token->user_id);
            $user->admin = true;
            $user->company_id = $user->id;
        }
        else
        {
            $user = Expert::find($token->user_id);
            $user->admin = false;
        }
        $this->auth->setUser( $user );
        return $next($request);
    }
}
