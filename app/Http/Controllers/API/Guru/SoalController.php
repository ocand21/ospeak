<?php

namespace App\Http\Controllers\API\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\Soal;
use Image;

use App\Topik;
class SoalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $soal = Soal::get();

        return response()->json([
          'soal' => $soal,
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
          'soal' => 'required',
          'id_topik' => 'required',
          'instruksi' => 'required',
        ]);

        DB::beginTransaction();
        try {
          Soal::create([
            'soal' => $request->soal,
            'id_topik' => $request->id_topik,
            'instruksi' => $request->instruksi,
          ]);
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
    public function show($id)
    {
        $soal = Soal::findOrFail($id);

        return response()->json([
          'soal' => $soal,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

      $soal = Soal::findOrFail($id);

      $request->validate([
        'soal' => 'required',
        'id_topik' => 'required',
        'instruksi' => 'required',
      ]);

      DB::beginTransaction();
      try {
        $soal->update([
          'soal' => $request->soal,
          'id_topik' => $request->id_topik,
          'instruksi' => $request->instruksi,
        ]);
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
    public function destroy($id)
    {
      $soal = Soal::findOrFail($id);

      DB::beginTransaction();
      try {
        $soal->delete();
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
