<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function login(Request $request)
    {
        // Validasi input
        $loginData = $request->validate([
            'email' => 'required_without:name|email', // Hanya butuh salah satu (email atau name)
            'name' => 'required_without:email|string', // Jika name diisi, email tidak perlu
            'password' => 'required',
        ]);

        // Cek apakah yang dikirim adalah email atau name
        if ($request->has('email')) {
            // Jika email yang dikirim, cari pengguna berdasarkan email
            $user = User::where('email', $request->email)->first();
        } else {
            // Jika name yang dikirim, cari pengguna berdasarkan name
            $user = User::where('name', $request->name)->first();
        }

        // Jika pengguna tidak ditemukan
        if (!$user) {
            return response([
                'message' => ['Email atau name tidak ditemukan'],
            ], 404);
        }

        // Cek password yang diberikan
        if (!Hash::check($request->password, $user->password)) {
            return response([
                'message' => ['Password salah'],
            ], 404);
        }

        // Jika berhasil, buat token autentikasi
        $token = $user->createToken('auth_token')->plainTextToken;

        // Kirimkan respons dengan data pengguna dan token
        return response([
            'user' => $user,
            'token' => $token,
        ], 200);
    }


    //logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logout success',
        ]);
    }
}
