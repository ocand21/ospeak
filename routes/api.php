<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'guru'], function(){

  Route::group(['middleware' => 'auth:api'], function(){

    Route::get('/soal/{id_topik}', 'API\Guru\SoalController@getSoal');
    Route::post('/soal/update/{uid}', 'API\Guru\SoalController@update');

    Route::get('/topik/{uid_kelas}/{id_tingkat}', 'API\Guru\TopikController@getTopic');
    Route::post('/topik/update/{uid}', 'API\Guru\TopikController@update');

    Route::post('/kelas/update/{uid}', 'API\Guru\KelasController@update');

    Route::apiResources([
      'soal' => 'API\Guru\SoalController',
      'kelas' => 'API\Guru\KelasController',
      'topik' => 'API\Guru\TopikController',
    ]);


    Route::post('profil/update/photo', 'API\Guru\ProfilController@uploadPhoto');
    Route::post('profil/update', 'API\Guru\ProfilController@updateProfil');

  });

  Route::post('register', 'API\Guru\AuthController@register');
  Route::post('login', 'API\Guru\AuthController@login');
});

Route::group(['prefix' => 'murid'], function(){

    Route::group(['middleware' => 'auth:api'], function(){

      Route::post('profil/update/photo', 'API\Murid\ProfilController@uploadPhoto');
      Route::post('profil/update', 'API\Murid\ProfilController@updateProfil');

      Route::get('/soal/{id_topik}', 'API\Murid\KelasController@getSoal');
      Route::get('/topik/{uid_kelas}/{id_tingkat}', 'API\Murid\KelasController@getTopic');
      Route::get('/tingkat/{uid_kelas}', 'API\Murid\KelasController@getTingkat');

      Route::post('kelas/add', 'API\Murid\KelasController@addClass');
      Route::get('kelas', 'API\Murid\KelasController@getClass');
      Route::get('logout', 'API\Murid\AuthController@logout');

    });

    Route::post('register', 'API\Murid\AuthController@register');
    Route::post('login', 'API\Murid\AuthController@login');

});
