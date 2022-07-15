<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Inventario;


class InventarioController extends Controller
{
    

    public function listarArticuloinventario(Request $request)
    {
         //if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if($buscar==''){
            $inventarios = Inventario::join('articulos','inventarios.idproducto', '=', 'articulos.id')
            ->join('categorias', 'articulos.idcategoria', '=', 'categorias.id')
            ->join('gramajes', 'articulos.idgramaje', '=', 'gramajes.id')
            ->select('articulos.id','articulos.idcategoria','articulos.idgramaje', 'articulos.nombre', 
            'categorias.nombre as nombre_categoria','gramajes.gramaje as nombre_gramaje',  'articulos.concentracion',
            'articulos.administracion','articulos.presentacion','articulos.items','articulos.condicion'
            )->orderBy('articulos.id','desc')->paginate(10);
        }
        else{
          
        }
    }
}
