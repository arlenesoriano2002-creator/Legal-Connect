namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // ❌ Not logged in
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // ❌ Not staff
        if ($user->role !== 'staff') {
            abort(403, 'Unauthorized Access');
        }

        // ✅ Allow access
        return $next($request);
    }
}