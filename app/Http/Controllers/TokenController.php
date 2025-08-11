<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TokenController extends Controller
{
    public function index()
    {
        $user = Auth::user(); // o User::find(1) si es admin único
        $tokens = $user->tokens;
        return view('tokens.index', compact('tokens'));
    }

    public function store(Request $request)
    {
        $user = Auth::user(); // o User::find(1)
        $token = $user->createToken($request->input('token_name'))->plainTextToken;
        return redirect()->back()->with('token_generado', $token);
    }

    public function destroy($tokenId)
    {
        $user = Auth::user(); // o User::find(1)
        $user->tokens()->where('id', $tokenId)->delete();

        return redirect()->back()->with('mensaje', 'Token revocado con éxito.');
    }
}
