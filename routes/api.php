<?php

/**
 * Definición de rutas de la API
 * 
 * Formato: método|ruta|controlador@método
 */

return [
    // Rutas de actividades
    'GET|/actividades' => 'ActividadesController@index',
    'POST|/actividades' => 'ActividadesController@store',
    'GET|/actividades/{id}' => 'ActividadesController@show',
    'PUT|/actividades/{id}' => 'ActividadesController@update',
    'DELETE|/actividades/{id}' => 'ActividadesController@destroy',
    
    // Rutas de ofertas
    'GET|/ofertas' => 'OfertasController@index',
    'POST|/ofertas' => 'OfertasController@store',
    'GET|/ofertas/{id}' => 'OfertasController@show',
    'PUT|/ofertas/{id}' => 'OfertasController@update',
    'POST|/ofertas/{id}/documentos' => 'OfertasController@uploadDocument',
    'GET|/ofertas/export/excel' => 'OfertasController@exportExcel',
];
