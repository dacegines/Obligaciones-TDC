<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('superUsuario')) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }


        $notificaciones = [];

        return view('gestion_cumplimiento.notificaciones.index', compact('notificaciones'));
    }
}
