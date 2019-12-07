<?php

namespace App\Http\Controllers\API\Murid;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\User;
use App\Murid;
use App\KelasMurid;
use App\Kelas;
use App\Tingkat;
use App\Topik;
class KelasController extends Controller
{

    public function getSoal($id_topik){

      $topik = Topik::findOrFail($id_topik);

      if ($topik->id_tingkat == '1') {
        $soal = DB::table('soal')
                    ->join('topik', 'topik.id', 'soal.id_topik')
                    ->join('tingkat', 'tingkat.id', 'topik.id_tingkat')
                    ->select(DB::raw("soal.id, soal.soal, soal.instruksi, topik.title, tingkat.tingkat"))
                    ->where('soal.id_topik', $id_topik)
                    ->inRandomOrder()
                    ->limit(10)
                    ->get();
      } elseif ($topik->id_tingkat == '2') {
        $soal = DB::table('soal')
                    ->join('topik', 'topik.id', 'soal.id_topik')
                    ->join('tingkat', 'tingkat.id', 'topik.id_tingkat')
                    ->select(DB::raw("soal.id, soal.soal, soal.instruksi, topik.title, tingkat.tingkat"))
                    ->where('soal.id_topik', $id_topik)
                    ->inRandomOrder()
                    ->limit(3)
                    ->get();
      } elseif ($topik->id_tingkat == '3') {
        $soal = DB::table('soal')
                    ->join('topik', 'topik.id', 'soal.id_topik')
                    ->join('tingkat', 'tingkat.id', 'topik.id_tingkat')
                    ->select(DB::raw("soal.id, soal.soal, soal.instruksi, topik.title, tingkat.tingkat"))
                    ->where('soal.id_topik', $id_topik)
                    ->inRandomOrder()
                    ->limit(1)
                    ->get();
      } else {
        return response()->json([
          'msg' => 'Topik tidak ditemukan',
        ]);
      }

      return response()->json([
        'soal' => $soal,
      ]);

    }

    public function getTopic($uid_kelas, $id_tingkat){
      $topik = Topik::where([['uid_kelas', $uid_kelas], ['id_tingkat', $id_tingkat]])->get();

      return response()->json([
        'topik' => $topik,
        'uid_kelas' => $uid_kelas,
        'id_tingkat' => $id_tingkat,
      ]);
      
    }

    public function getTingkat($uid){
      $tingkat = Tingkat::get();

      return response()->json([
        'tingkat' => $tingkat,
        'uid_kelas' => $uid,
      ]);
    }

    public function addClass(Request $request){

      $request->validate([
        'uid_kelas' => 'required',
      ]);

      $user = auth('api')->user();

      $kelas = Kelas::where('uid', $request->uid_kelas)->first();
      if ($kelas) {
        $klMurid = KelasMurid::where([['uid_murid', $user->uid],['uid_kelas', $kelas->uid]])->first();
        if ($klMurid) {
          return response()->json([
            'msg' => 'Anda telah tergabung dalam kelas tersebut'
          ]);

        } else {
          $newClass = KelasMurid::create([
            'uid_murid' => $user->uid,
            'uid_kelas' => $kelas->uid,
          ]);

          return response()->json([
            'msg' => 'Kelas berhasil ditambahkan',
          ]);
        }
      } else {
        return response()->json([
          'msg' => 'Kelas tidak ditemukan',
        ]);
      }

    }

    public function getClass(){

      $user = auth('api')->user();

      $kelas = DB::table('kelas')
                  ->join('kelas_murid', 'kelas_murid.uid_kelas', 'kelas.uid')
                  ->select(DB::raw("kelas.id, kelas.uid, kelas.nama, kelas.foto"))
                  ->where('kelas_murid.uid_murid', $user->uid)
                  ->get();

      return response()->json([
        'kelas' => $kelas,
      ]);

    }
}
