<?php

namespace App\Http\Controllers\API\Murid;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\User;
use App\Murid;

use Image;
class ProfilController extends Controller
{
    public function uploadPhoto(Request $request){
      $user = auth('api')->user();

      $request->validate([
        'photo' => 'required|'
      ]);

      $murid = Murid::where('uid', $user->uid)->first();
      $photo = $request->photo;
      DB::beginTransaction();
      try {
        if ($photo != '') {
          $filename = "murid-".time().".png";
          // $ekstensi = explode('/', explode(':', substr($photo, 0, strpos($photo, ';')))[1])[1];
          Image::make($photo)->save(public_path('/img/murid/').$filename);

          $murid->update([
            'photo' => '/img/murid/' . $filename,
          ]);
        }
      } catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }

      DB::commit();

      return response()->json([
        'msg' => 'Foto berhasil diupload',
        'foto' => '/img/murid/' . $filename,
      ]);

    }

    public function updateProfil(Request $request){
      $users = auth('api')->user();

      $request->validate([
        'name' => 'required',
        'email' => 'required|unique:users,credential,'.$users->id,
        'phone' => 'required|unique:murid,phone,'.$users->id,
        'institution' => 'sometimes',
        'age' => 'sometimes',
        'gender' => 'sometimes',
      ]);

      DB::beginTransaction();
      try {
        $murid = Murid::where('uid', $users->uid)->first();
        $murid->update([
          'name' => $request->name,
          'email' => $request->email,
          'phone' => $request->phone,
          'institution' => $request->institution,
          'age' => $request->age,
          'gender' => $request->gender,
        ]);
      } catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }

      try {
        $usr = User::findOrFail($users->id);
        $usr->update([
          'credential' => $murid->email,
        ]);
      } catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }

      DB::commit();

      return response()->json([
        'msg' => 'Profil diupdate',
        'profil' => $murid,
      ]);

    }
}
