<?php

namespace App\Services;

use App\Models\Oferta;

/**
 * Servicio para generar consecutivos de ofertas
 * 
 * Formato: O-{000N}-{YY}
 * Ejemplo: O-0001-25
 */
class ConsecutivoService
{
    private Oferta $ofertaModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ofertaModel = new Oferta();
    }

    /**
     * Genera un nuevo consecutivo único
     * 
     * @return string
     */
    public function generate(): string
    {
        $year = date('y'); // Año en formato YY (ej: 25)
        $lastNumber = $this->ofertaModel->getLastConsecutiveNumber($year);
        $nextNumber = $lastNumber + 1;
        
        $consecutivo = sprintf('O-%04d-%s', $nextNumber, $year);
        
        // Verificar que no exista (por si acaso)
        $attempts = 0;
        while ($this->ofertaModel->consecutivoExists($consecutivo) && $attempts < 100) {
            $nextNumber++;
            $consecutivo = sprintf('O-%04d-%s', $nextNumber, $year);
            $attempts++;
        }
        
        if ($attempts >= 100) {
            throw new \Exception('No se pudo generar un consecutivo único después de múltiples intentos');
        }
        
        return $consecutivo;
    }

    /**
     * Valida el formato de un consecutivo
     * 
     * @param string $consecutivo
     * @return bool
     */
    public function isValidFormat(string $consecutivo): bool
    {
        return preg_match('/^O-\d{4}-\d{2}$/', $consecutivo) === 1;
    }
}
