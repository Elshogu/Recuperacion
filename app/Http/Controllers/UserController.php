<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // GET /api/users
    public function index()
    {
        return User::with('image')->orderBy('name')->get();
    }

    // GET /api/users/{id}
    public function show($id)
    {
        return User::with('image')->findOrFail($id);
    }

    // ...
    // POST /api/users
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email', // Asegúrate de que el email no exista
            'phone' => 'required|string|max:20',
            'image' => 'nullable|file|mimes:jpeg,png,jpg|max:5120' // Usar 'file' y 'mimes' específicos
        ]);

        // Usar $request->input() en caso de problemas con $request->only()awdaw
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
        ]);
        
        // El resto es correcto
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('users', 'public');
            $user->image()->create(['url' => Storage::url($path)]);
        }

        return response()->json($user->load('image'), 201);
    }
    // ...

    // PUT /api/users/{id}
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'  => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'image' => 'nullable|image|max:2048'
        ]);

        $user = User::findOrFail($id);
        $user->update($request->only('name', 'email', 'phone'));

        if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            $user->image?->delete();

            $path = $request->file('image')->store('users', 'public');
            $user->image()->create(['url' => Storage::url($path)]);
        }

        return response()->json($user->load('image'));
    }

    // DELETE /api/users/{id}
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->image?->delete(); // eliminar imagen asociada
        $user->delete();

        return response()->noContent();
    }
}