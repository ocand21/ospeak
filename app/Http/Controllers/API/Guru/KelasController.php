<?php

namespace App\Http\Controllers\API\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\Kelas;
use Image;
class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $guru = auth('api')->user();

        $kelas = Kelas::where('uid_guru', $guru->uid)->get();

        return response()->json([
          'kelas' => $kelas,
        ]);
    }

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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
          'nama' => 'required',
          'foto' => 'sometimes',
        ]);

        DB::beginTransaction();
        try {
          $guru = auth('api')->user();
          $kelas = new Kelas();
          $kelas->uid = Str::random(7);
          $kelas->uid_guru = $guru->uid;
          $kelas->nama = $request->nama;
          $foto = $request->foto;
          if ($foto != '') {
            $filename = "kelas-".time().".png";
            Image::make($foto)->save(public_path('/img/kelas/').$filename);

            $kelas->foto = '/img/kelas/' . $filename;
          }
          $kelas->save();
        } catch (\Exception $e) {
          DB::rollback();
          throw $e;
        }

        DB::commit();

        return response()->json([
          'msg' => 'Berhasil ditambah',
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($uid)
    {
        $kelas = Kelas::where('uid', $uid)->first();

        $murid = DB::table('kelas')
                      ->join('kelas_murid', 'kelas_murid.uid_kelas', 'kelas.uid')
                      ->join('murid', 'murid.uid', 'kelas_murid.uid_murid')
                      ->select(DB::raw("murid.uid, murid.name, murid.email, murid.phone, murid.photo,
                      murid.institution, murid.gender, murid.age"))
                      ->where('kelas.uid', $uid)
                      ->get();


        return response()->json([
          'kelas' => $kelas,
          'murid' => $murid,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $uid)
    {
        $kelas = Kelas::where([['uid', $uid],['uid_guru', $guru->uid]])->first();

        $request->validate([
          'nama' => 'required|unique:kelas,nama,'.$kelas->id,
          'foto' => 'sometimes',
        ]);

        // dd($request->nama);

        DB::beginTransaction();
        try {
          $foto = $request->foto;
          $kelas->nama = $request->nama;
          if ($foto != '') {
            $filename = "kelas-".time().".png";
            Image::make($foto)->save(public_path('/img/kelas/').$filename);
            $oldFilename = '/img/kelas/' . $kelas->foto;
            // unlink($oldFilename);
            $kelas->foto = '/img/kelas/' . $filename;
          }
          $kelas->save();
        } catch (\Exception $e) {
          DB::rollback();
          throw $e;
        }

        DB::commit();

        return response()->json([
          'msg' => 'Berhasil diupdate',
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($uid)
    {
        $guru = auth('api')->user();
        $kelas = Kelas::where([['uid', $uid],['uid_guru', $guru->uid]])->first();

        DB::beginTransaction();
        try {
          // unlink($kelas->foto);
          $kelas->delete();
        } catch (\Exception $e) {
          DB::rollback();
          throw $e;
        }

        DB::commit();

        return response()->json([
          'msg' => 'Berhasil dihapus',
        ]);

    }
}
