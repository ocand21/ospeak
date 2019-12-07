<?php

namespace App\Http\Controllers\API\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Str;

use App\User;
use App\Guru;
class AuthController extends Controller
{
    public function logout(Request $request){
      $request->user()->token()->revoke();

      return response()->json([
        'msg' => 'Logout berhasil',
      ]);
    }

    public function register(Request $request){

      $request->validate([
        'name' => 'required',
        'phone' => 'required|unique:murid',
        'email' => 'required|unique:murid',
        'password' => 'required|string',
      ]);

      DB::beginTransaction();
      try {
        $guru = Guru::create([
          'uid' => Str::random(20),
          'name' => $request->name,
          'phone' => $request->phone,
          'email' => $request->email,
        ]);
      } catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }

      try {
        User::create([
          'uid' => $guru->uid,
          'credential' => $guru->email,
          'password' => bcrypt($request->password),
          'tipe' => 'Guru',
        ]);
      } catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }

      DB::commit();

      return response()->json([
        'msg' => 'Berhasil register',
      ]);

    }

    public function login(Request $request){

      $request->validate([
        'credential' => 'required',
        'password' => 'required|string',
        'tipe' => 'required',
      ]);

      $credentials = request(['credential', 'password']);

      if (!Auth::attempt(['credential' => $request->credential, 'password' => $request->password, 'tipe' => $request->tipe])) {
        return response()->json([
          'msg' => 'Akun tidak ditemukan. Hubungi administrasi',
        ]);
      }

      $user = $request->user();

      $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me) {
          $token->expires_at = Carbon::now()->addWeeks(1);
        }

        $token->save();

        $token = $tokenResult->accessToken;
        $token_type = 'Bearer';
        $accessToken = $token_type . ' ' . $token;

      $dtl = DB::table('users')
                  ->join('guru', 'guru.uid', 'users.uid')
                  ->select(DB::raw("guru.uid, guru.name, guru.email, guru.phone, guru.photo, guru.institution,
                  guru.gender, guru.age"))
                  ->where('guru.email', $request->credential)
                  ->first();


      return response()->json([
        'murid' => $dtl,
        'access_token' => $accessToken,
        'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
      ]);

    }
}
