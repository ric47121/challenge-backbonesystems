<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class ZipCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($zip_code)
    {
        $res = Redis::get($zip_code);

        if($res){
            // return json_decode($res);
            return ($res);
        }

// dd($res);
        $path = storage_path().'/CPdescarga.txt';
        $arch = file_get_contents_utf8($path);

        $lines = explode("\n", $arch);

        $buscar = $zip_code;
        
        $arr = [];

        $encontro = false;
        for ($i=0; $i < count($lines); $i++) { 
            $parts = explode("|", $lines[$i]);
            if(trim($parts[0]) == $buscar ){

                $arr['zip_code'] = $buscar;
                $arr['locality'] = procesar_str($parts[5]);
                // $arr['locality'] = utf8_encode("uuñ");
                
                $federal_entity = [];
                $federal_entity['key'] = procesar_int($parts[7]);
                $federal_entity['name'] = procesar_str($parts[4]);
                $federal_entity['code'] = procesar_int($parts[9]);

                $arr['federal_entity'] = $federal_entity;

                $j=$i;
                $parts_2 = explode("|", $lines[$j]);
                $settlements = [];
                while( $j < count($lines) && trim($parts_2[0]) == $buscar ){

                        $data = [];
                        $data['key'] = procesar_int($parts_2[12]);
                        $data['name'] = procesar_str($parts_2[1]);
                        $data['zone_type'] = procesar_str($parts_2[13]);


                        $settlement_type = [];
                        $settlement_type['name'] = procesar_str_sin_uppercase($parts_2[2]);
                        $data['settlement_type'] = $settlement_type;

                        $settlements[] = $data;
                        $j++;
                        $parts_2 = explode("|", $lines[$j]);

                }

                $arr['settlements'] = $settlements;

                $municipality = [];
                $municipality['key'] = procesar_int($parts[11]);
                $municipality['name'] = procesar_str($parts[3]);

                $arr['municipality'] = $municipality;

                $encontro = true;
                Redis::set($zip_code, json_encode($arr));
                break;
            }
        }

        if(!$encontro) abort(500);

        return $arr;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
