<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Alamat email atau password salah.'],
            ]);
        }

        $token = $user->createToken('flutter-mobile-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil login',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 200);
    }
    public function loginGoogle(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string',
            'google_id' => 'required|string',
            'location_name' => 'nullable|string'
        ]);

        $user = User::where('email', $request->email)->first();

        // Cari region_id berdasarkan location_name (Desa/Kecamatan)
        $regionId = 1; // Default
        if ($request->location_name) {
            // Find a region matching the location name (e.g. "Pematang Duku Timur")
            $locName = trim($request->location_name);
            $region = \App\Models\Region::where('name', 'like', "%{$locName}%")->first();
            if ($region) {
                $regionId = $region->id;
            }
        }

        if (!$user) {
            // Register if not found
            // In a real app, you might want to prompt for NIK etc.
            // But for now, we'll create a minimal user account.
            $user = User::create([
                'email' => $request->email,
                'name' => $request->name,
                'username' => $request->email, // use email as username
                'password' => Hash::make(uniqid()), // random password
                'nik' => null, 
                'phone' => '-',
                'address' => $request->location_name ?? '-',
                'gender' => 'laki-laki',
                'region_id' => $regionId,
            ]);
        } else {
            // Update user's region_id if they log in via Google and we found their location
            if ($request->location_name && $regionId != 1) {
                $user->update(['region_id' => $regionId]);
            }
        }

        $token = $user->createToken('flutter-mobile-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil login dengan Google',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil logout'
        ], 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'region_id' => 'required|exists:regions,id',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'nik' => null,
            'username' => $request->username,
            'name' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => '-',
            'gender' => 'laki-laki',
            'region_id' => $request->region_id,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('flutter-mobile-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 201);
    }

    public function user(Request $request)
    {
        $user = $request->user()->load('file');

        $kecamatan_name = 'Belum ditentukan';
        $desa_name = 'Belum ditentukan';
        $rw_name = 'Belum ditentukan';
        $rt_name = 'Belum ditentukan';

        if ($user->region_id) {
            $currentRegion = \App\Models\Region::find($user->region_id);

            while ($currentRegion) {
                if ($currentRegion->type == 'rt') {
                    $rt_name = $currentRegion->name;
                } elseif ($currentRegion->type == 'rw') {
                    $rw_name = $currentRegion->name;
                } elseif ($currentRegion->type == 'desa') {
                    $desa_name = $currentRegion->name;
                } elseif ($currentRegion->type == 'kecamatan') {
                    $kecamatan_name = $currentRegion->name;
                }

                if ($currentRegion->parent_id) {
                    $currentRegion = \App\Models\Region::find($currentRegion->parent_id);
                } else {
                    $currentRegion = null;
                }
            }
        }

        $avatar_url = null;
        if ($user->file && Storage::disk('local')->exists($user->file->path)) {
            $avatar_url = route('media.profile', ['filename' => $user->file->filename]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'region_info' => [
                    'kecamatan' => $kecamatan_name,
                    'desa' => $desa_name,
                    'rw' => $rw_name,
                    'rt' => $rt_name,
                ],
                'avatar_url' => $avatar_url,
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'gender' => 'nullable|in:laki-laki,perempuan',
            'profile' => 'nullable|image|mimes:jpg,jpeg,png|max:8192',
        ]);

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'rt' => $validated['rt'] ?? $user->rt,
            'rw' => $validated['rw'] ?? $user->rw,
            'gender' => $validated['gender'],
        ]);

        if ($request->hasFile('profile')) {
            if ($user->file) {
                if (Storage::disk('local')->exists($user->file->path)) {
                    Storage::delete($user->file->path);
                }
                $user->file->delete();
            }

            $file = $request->file('profile');
            $extension = $file->getClientOriginalExtension();
            $filename = $user->id . '_' . time() . '.' . $extension;
            $path = $file->storeAs('profiles', $filename, ['disk' => 'local']);

            $user->file()->create([
                'alias' => 'profile_picture',
                'filename' => $filename,
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui'
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini tidak cocok.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diubah',
        ]);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();
        $user->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'FCM Token berhasil diperbarui',
        ]);
    }
}
