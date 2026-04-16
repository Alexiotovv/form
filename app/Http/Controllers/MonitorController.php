<?php

namespace App\Http\Controllers;

use App\Models\TMovim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MonitorController extends Controller
{
    /**
     * Mostrar últimos movimientos recibidos (Solo admin)
     * Tabla principal: TMovim | Modal: TMovimDet relacionados
     */
    public function index()
    {
        // 🔐 Verificar permiso de administrador
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403, 'Acceso restringido a administradores');
        }
        
        // 📦 Cache por 30 segundos para evitar consultas repetidas
        $movimientos = Cache::remember('monitor_tmovim_recientes', 30, function() {
            return TMovim::select([
                    'id', 'movcoditip', 'movnumero', 'almcodiorg', 'almcodidst',
                    'prvdescrip', 'movtot', 'movfechult', 'movsitua', 
                    'created_at', 'updated_at'
                ])
                ->withCount('detalles') // Contar cuántos productos tiene cada movimiento
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        });
        
        // 🟢🟡🔴 Calcular estado visual del script
        $ultimoRegistro = TMovim::select('created_at')
            ->orderBy('created_at', 'desc')
            ->first();
            
        $estadoScript = $this->calcularEstadoScript($ultimoRegistro);
        
        // 📊 Estadísticas rápidas (cache 60 segundos)
        $estadisticas = Cache::remember('monitor_tmovim_stats', 60, function() {
            return [
                'total_hoy' => TMovim::whereDate('created_at', today())->count(),
                'total_ultima_hora' => TMovim::where('created_at', '>=', now()->subHour())->count(),
                'ultimo_movimiento' => TMovim::max('movfechult'),
                'total_detalles_hoy' => \App\Models\TMovimDet::whereDate('created_at', today())->count(),
            ];
        });
        
        return view('monitor.tmovim', compact(
            'movimientos', 
            'estadoScript', 
            'ultimoRegistro',
            'estadisticas'
        ));
    }
    
    /**
     * API para obtener detalles de un movimiento específico (para el modal)
     */
    public function apiDetallesMovimiento($movnumero)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        
        $detalles = \App\Models\TMovimDet::where('movnumero', $movnumero)
            ->select(['id', 'medcod', 'medlote', 'medfechvto', 'movcantid', 'movprecio', 'movtotal', 'movsitua'])
            ->orderBy('medcod')
            ->get();
            
        $movimiento = \App\Models\TMovim::select(['movnumero', 'prvdescrip', 'movtot', 'movfechult'])
            ->where('movnumero', $movnumero)
            ->first();
        
        return response()->json([
            'movimiento' => $movimiento,
            'detalles' => $detalles,
            'total' => $detalles->count()
        ]);
    }
    
    /**
     * API endpoint para polling AJAX del estado general (opcional)
     */
    public function apiUltimoRegistro()
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        
        $ultimo = TMovim::select('id', 'created_at', 'movfechult', 'movnumero')
            ->orderBy('created_at', 'desc')
            ->first();
            
        return response()->json([
            'ultimo_registro' => $ultimo,
            'hace' => $ultimo ? $ultimo->created_at->diffForHumans() : 'Nunca',
            'activo' => $ultimo ? $ultimo->created_at->gt(now()->subMinutes(30)) : false,
            'timestamp' => $ultimo?->created_at?->timestamp
        ]);
    }
    
    /**
     * Determinar estado y mensaje visual del script
     */
    private function calcularEstadoScript($ultimoRegistro): array
    {
        if (!$ultimoRegistro) {
            return [
                'clase' => 'danger',
                'icono' => '🔴',
                'texto' => 'Sin actividad',
                'descripcion' => 'El script no ha enviado movimientos aún',
                'minutos' => null
            ];
        }
        
        $minutos = $ultimoRegistro->created_at->diffInMinutes(now());
        
        if ($minutos <= 5) {
            return [
                'clase' => 'success',
                'icono' => '🟢',
                'texto' => 'Activo',
                'descripcion' => "Último movimiento hace {$minutos} minuto(s)",
                'minutos' => $minutos
            ];
        } elseif ($minutos <= 30) {
            return [
                'clase' => 'warning',
                'icono' => '🟡',
                'texto' => 'Pausa reciente',
                'descripcion' => "Último movimiento hace {$minutos} minuto(s)",
                'minutos' => $minutos
            ];
        } else {
            return [
                'clase' => 'danger',
                'icono' => '🔴',
                'texto' => 'Inactivo',
                'descripcion' => "Último movimiento hace {$minutos} minuto(s) - Verificar script local",
                'minutos' => $minutos
            ];
        }
    }
    
    /**
     * Refresh manual de cache
     */
    public function refresh(Request $request)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403);
        }
        
        Cache::forget('monitor_tmovim_recientes');
        Cache::forget('monitor_tmovim_stats');
        
        return redirect()->back()->with('success', '✅ Datos actualizados correctamente');
    }
}