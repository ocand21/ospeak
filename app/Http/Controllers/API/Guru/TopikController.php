<?php

namespace App\Http\Controllers\API\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\Topik;
use Image;
class TopikController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $topik = Topik::get();


        return response()->json([
            'topik' => $topik,
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
          'title' => 'required',
          'uid_kelas' => 'required',
          'id_tingkat' => 'required',
        ]);

        DB::beginTransaction();
        try {
          Topik::create([
            'title' => $request->title,
            'uid_kelas' => $request->uid_kelas,
            'id_tingkat' => $request->id_tingkat,
          ]);
        } catch (\Exception $e) {
          DB::rollback();
          throw $e;
        }

        DB::commit();

        return response()->json([
          'msg' => 'Berhasil tambah',
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
        $topik = Topik::findOrFail($id);

        return response()->json([
          'topik' => $topik,
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
      $topik = Topik::findOrFail($id);

      $request->validate([
        'title' => 'required',
        'uid_kelas' => 'required',
        'id_tingkat' => 'required',
      ]);

      DB::beginTransaction();
      try {
        $topik->update([
          'title' => $request->title,
          'uid_kelas' => $request->uid_kelas,
          'id_tingkat' => $request->id_tingkat,
        ]);
      } catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }

      DB::commit();

      return response()->json([
        'msg' => 'Berhasil update',
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
        $topik = Topik::findOrFail($id);

        DB::beginTransaction();
        try {
          $topik->delete();
        } catch (\Exception $e) {
          DB::rollback();
          throw $e;
        }

        DB::commit();

        return response()->json([
          'msg' => 'Berhasil hapus',
        ]);

    }
}
