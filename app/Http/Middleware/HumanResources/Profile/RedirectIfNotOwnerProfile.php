<?php

namespace App\Http\Middleware\HumanResources\Profile;

use Closure;

class RedirectIfNotOwnerProfile
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {

    if (!$request->user()->isOwner($request->route()->profile->id)) {
      return redirect()->back();
    }

    return $next($request);
  }
}
