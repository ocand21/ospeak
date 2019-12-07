<?php

namespace App\Http\Controllers\API\Murid;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Str;

use App\User;
use App\Murid;
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
        $murid = Murid::create([
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
          'uid' => $murid->uid,
          'credential' => $murid->email,
          'password' => bcrypt($request->password),
          'tipe' => 'Murid',
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
                  ->join('murid', 'murid.uid', 'users.uid')
                  ->select(DB::raw("murid.uid, murid.name, murid.email, murid.phone, murid.photo, murid.institution,
                  murid.gender, murid.age"))
                  ->where('murid.email', $request->credential)
                  ->first();


      return response()->json([
        'murid' => $dtl,
        'access_token' => $accessToken,
        'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
      ]);

    }

}
