<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class ZipCodeController extends Controller
{

    private function generate_indices_in_redis(){
        $handle_ori = fopen(storage_path().'/CPdescarga.txt', "r");
        
        $ultimo = 0;
        $todo =[];
        $posiciones =[];
        $position_init_line = ftell($handle_ori);
        while (($raw_string = fgets($handle_ori)) !== false) {
            $position_end_line = ftell($handle_ori);

            if (stristr($raw_string,'|')) {
                $parts = explode("|", $raw_string);
                if( strlen($parts[0]) > 0 && strlen($parts[0]) < 10 &&  is_numeric($parts[0]) && intval($parts[0]) != $ultimo ){
                        $todo[] = $parts[0];
                        $posiciones[] = $position_init_line;
                    $ultimo = intval($parts[0]);
                }	
            }
            $position_init_line = $position_end_line;
        }
        fclose($handle_ori);
        Redis::set('arr_indices', json_encode($todo));
        Redis::set('arr_posiciones', json_encode($posiciones));
    }

    private function generate_file_indices(){
        $handle_ori = fopen(storage_path().'/CPdescarga.txt', "r");
        $handle = fopen(storage_path()."/CPdescarga_indices.txt", "w") or die("Error creando archivo");

        $ultimo = 0;
        $position_init_line = ftell($handle_ori);
        while (($raw_string = fgets($handle_ori)) !== false) {
            $position_end_line = ftell($handle_ori);

            if (stristr($raw_string,'|')) {
                $parts = explode("|", $raw_string);
                if( strlen($parts[0]) > 0 && strlen($parts[0]) < 10 &&  is_numeric($parts[0]) && intval($parts[0]) != $ultimo ){
                    fwrite($handle, $parts[0].';'.$position_init_line."\n");
                    $ultimo = intval($parts[0]);
                }	
            }
            $position_init_line = $position_end_line;
        }
        fclose($handle_ori);
        fclose($handle);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($zip_code)
    {
        $arr_indices = Redis::get('arr_indices');
        $arr_posiciones = Redis::get('arr_posiciones');

        if(!$arr_indices){
            $this->generate_indices_in_redis();
        }

        if(intval($zip_code) == 0) abort(500);

        $arr_indices = json_decode($arr_indices);
        $arr_posiciones = json_decode($arr_posiciones);

        if($pos = get_position_element($arr_indices, count($arr_indices), $zip_code) ){
            return $this->get_data_from_position($arr_posiciones[$pos], $zip_code);
        }else{
            abort(500);
        }
        
    }

    public function index_indice_file($zip_code)
    {
        if(!file_exists(storage_path().'/CPdescarga_indices.txt'))
            $this->generate_file_indices();

        if(intval($zip_code) == 0) abort(500);

        $arch = file_get_contents(storage_path().'/CPdescarga_indices.txt');
        $lines = explode("\n", $arch);
        
        $arr = [];
        $arr_pos = [];
        for ($i=0; $i < count($lines); $i++) { 
            $pedazos = explode(";", $lines[$i]);
            $arr[] = $pedazos[0];  
            $arr_pos[] = (isset($pedazos[1]))? intval($pedazos[1]):0;
            // $arr_pos[] = $pedazos[1];
        }


        if($pos = get_position_element($arr, count($arr), $zip_code) ){
            return $this->get_data_from_position($arr_pos[$pos], $zip_code);
        }else{
            abort(500);
        }
        
    }


    private function get_data_from_position($position, $buscar){
    
        $handle = fopen(storage_path().'/CPdescarga.txt', "r");
        fseek($handle, $position);
    
        $arr = [];
        while (($raw_string = fgets_utf8($handle)) !== false) {
            
            if(stristr($raw_string, "|")){
    
                $parts = explode("|", $raw_string);
                if(intval($parts[0]) == $buscar ){
    
                    $arr['zip_code'] = $parts[0];
                    $arr['locality'] = procesar_str($parts[5]);
                    $federal_entity = [];
                    $federal_entity['key'] = procesar_int($parts[7]);
                    $federal_entity['name'] = procesar_str($parts[4]);
                    $federal_entity['code'] = procesar_int($parts[9]);
    
                    $arr['federal_entity'] = $federal_entity;
    
                    $parts_2 = explode("|", $raw_string);
                    $settlements = [];
                    while (($raw_string_2 = fgets_utf8($handle)) !== false && trim($parts_2[0]) == $buscar) {
                            $data = [];
                            $data['key'] = procesar_int($parts_2[12]);
                            $data['name'] = procesar_str($parts_2[1]);
                            $data['zone_type'] = procesar_str($parts_2[13]);
    
    
                            $settlement_type = [];
                            $settlement_type['name'] = procesar_str_sin_uppercase($parts_2[2]);
                            $data['settlement_type'] = $settlement_type;
    
                            $settlements[] = $data;
                            $parts_2 = explode("|", $raw_string_2);
                    }
    
                    $arr['settlements'] = $settlements;
    
                    $municipality = [];
                    $municipality['key'] = procesar_int($parts[11]);
                    $municipality['name'] = procesar_str($parts[3]);
    
                    $arr['municipality'] = $municipality;
    
                    $encontro = true;
                    break;
                }
    
            }else{
                continue;
            }
    
        }
    
        fclose($handle);
        return ($arr);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_redis($zip_code)
    {
        $res = Redis::get($zip_code);

        if($res){
            return ($res);
        }

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
