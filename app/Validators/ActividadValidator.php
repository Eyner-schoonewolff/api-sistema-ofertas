<?php

namespace App\Validators;

/**
 * Validador para actividades (UNSPSC)
 */
class ActividadValidator extends Validator
{
    /**
     * Define las reglas de validación para actividades
     * 
     * @return void
     */
    protected function rules(): void
    {
        // Validar código_segmento (obligatorio, numérico)
        $this->required('codigo_segmento', 'El código de segmento es obligatorio');
        $this->numeric('codigo_segmento', 'El código de segmento debe ser numérico');

        // Validar segmento (obligatorio)
        $this->required('segmento', 'El segmento es obligatorio');
        if (isset($this->data['segmento'])) {
            $this->maxLength('segmento', 200, 'El segmento no puede exceder 200 caracteres');
        }

        // Validar código_familia (obligatorio, numérico)
        $this->required('codigo_familia', 'El código de familia es obligatorio');
        $this->numeric('codigo_familia', 'El código de familia debe ser numérico');

        // Validar familia (obligatorio)
        $this->required('familia', 'La familia es obligatoria');
        if (isset($this->data['familia'])) {
            $this->maxLength('familia', 200, 'La familia no puede exceder 200 caracteres');
        }

        // Validar código_clase (obligatorio, numérico)
        $this->required('codigo_clase', 'El código de clase es obligatorio');
        $this->numeric('codigo_clase', 'El código de clase debe ser numérico');

        // Validar clase (obligatorio)
        $this->required('clase', 'La clase es obligatoria');
        if (isset($this->data['clase'])) {
            $this->maxLength('clase', 200, 'La clase no puede exceder 200 caracteres');
        }

        // Validar código_producto (obligatorio, numérico)
        $this->required('codigo_producto', 'El código de producto es obligatorio');
        $this->numeric('codigo_producto', 'El código de producto debe ser numérico');

        // Validar producto (obligatorio)
        $this->required('producto', 'El producto es obligatorio');
        if (isset($this->data['producto'])) {
            $this->maxLength('producto', 200, 'El producto no puede exceder 200 caracteres');
        }
    }
}
