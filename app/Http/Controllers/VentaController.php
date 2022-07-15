<?php

namespace App\Http\Controllers;

use App\DetalleVenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Venta;
use Carbon\Carbon;
use Exception;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar==''){
            $ventas = Venta::join('personas','ventas.idcliente','=','personas.id')
            ->join('users','ventas.idusuario','=','users.id')
            ->select('ventas.id','ventas.fecha_salida',
            'ventas.total','ventas.estado','personas.nombre',
            'users.usuario')
            ->orderBy('ventas.id', 'desc')->paginate(3);
        }
        else{
            $ventas = Venta::join('personas','ventas.idcliente','=','personas.id')
            ->join('users','ventas.idusuario','=','users.id')
            ->select('ventas.id','ventas.fecha_salida',
            'ventas.total','ventas.estado','personas.nombre',
            'users.usuario')
            ->where('ventas.'.$criterio, 'like', '%'. $buscar . '%')
            ->orderBy('ventas.id', 'desc')->paginate(3);
        }
        return [
            'pagination' => [
                'total'        => $ventas->total(),
                'current_page' => $ventas->currentPage(),
                'per_page'     => $ventas->perPage(),
                'last_page'    => $ventas->lastPage(),
                'from'         => $ventas->firstItem(),
                'to'           => $ventas->lastItem(),
            ],
            'ventas' => $ventas
        ];
    }

    public function obtenerCabecera(Request $request){

        if (!$request->ajax()) return redirect('/');
        $id = $request->id;
            $venta = Venta::join('personas','ventas.idcliente','=','personas.id')
            ->join('users','ventas.idusuario','=','users.id')
            ->select('ventas.id','ventas.fecha_salida',
            'ventas.total','ventas.estado','personas.nombre',
            'users.usuario')
            ->where('ventas.id', '=', $id)
            ->orderBy('ventas.id', 'desc')->take(1)->get();
        return ['venta' => $venta ];
    }

    public function obtenerDetalles(Request $request){

        if (!$request->ajax()) return redirect('/');
        $id = $request->id;
            $detalles = DetalleVenta::join('articulos','detalle_ventas.idarticulo','=','articulos.id')
            ->select('detalle_ventas.cantidad','detalle_ventas.precio','detalle_ventas.cantidad_blister',
            'articulos.nombre as articulo')
            ->where('detalle_ventas.idventa', '=', $id)
            ->orderBy('detalle_ventas.id', 'desc')->get();        
        return ['detalles' => $detalles ];
    }
    
    public function pdf(Request $request, $id){
        $venta = Venta::join('personas','ventas.idcliente','=','personas.id')
        ->join('users','ventas.idusuario','=','users.id')
        ->select('ventas.id','ventas.created_at',
        'ventas.total','ventas.estado','personas.nombre','personas.tipo_documento','personas.num_documento',
        'personas.direccion','personas.email','personas.telefono',
        'users.usuario')
        ->where('ventas.id', '=', $id)
        ->orderBy('ventas.id', 'desc')->take(1)->get();

        $detalles = DetalleVenta::join('articulos','detalle_ventas.idarticulo','=','articulos.id')
        ->select('detalle_ventas.cantidad','detalle_ventas.precio',
        'articulos.nombre as articulo')
        ->where('detalle_ventas.idventa', '=', $id)
        ->orderBy('detalle_ventas.id', 'desc')->get();

        $numventa=Venta::select('id')->where('id',$id)->get();

        $pdf = \PDF::loadView('pdf.venta',['venta'=>$venta, 'detalles'=>$detalles]);
        return  $pdf->download('venta-'.$numventa[0]->num_comprobante.'.pdf');
    }

    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        
        try{
            DB::beginTransaction();
            $fecha = Carbon::now();
            $mytime = Carbon::now('America/El_Salvador');
            $venta = new Venta();
            $venta->idcliente = $request->idcliente;
            $venta->idusuario = \Auth::user()->id;
            $venta->fecha_salida = $request->fecha_salida;
            $venta->total = $request->total;
            $venta->estado = 'Registrado';
            $venta->save();

            $detalles = $request->data;//Array de detalles
            //recorrer todos los elementos
            foreach ($detalles as $ep => $det)
             {
                $detalle = new DetalleVenta();
                $detalle->idventa = $venta->id;
                $detalle->idarticulo = $det['idarticulo'];
                $detalle->cantidad = $det['cantidad'];
                $detalle->cantidad_blister = $det['cantidad_blister'];
                $detalle->precio = $det['precio'];
                $detalle->save();
            }

            DB::commit();
            return[ 
                'id' => $venta->id
            ];

        } catch (Exception $e){
            DB::rollBack();
        }
   
    }

    public function desactivar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $venta = Venta::findOrFail($request->id);
        $venta->estado = 'Anulada';
        $venta->save();
    }
}
