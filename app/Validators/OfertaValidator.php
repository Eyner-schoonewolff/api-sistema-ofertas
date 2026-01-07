<?php

namespace App\Validators;

/**
 * Validador para ofertas
 */
class OfertaValidator extends Validator
{
    /**
     * Define las reglas de validación para ofertas
     * 
     * @return void
     */
    protected function rules(): void
    {
        // Validar objeto (obligatorio, máximo 150 caracteres)
        $this->required('objeto', 'El objeto de la oferta es obligatorio');
        if (isset($this->data['objeto'])) {
            $this->maxLength('objeto', 150, 'El objeto no puede exceder 150 caracteres');
        }

        // Validar descripción (obligatorio, máximo 400 caracteres)
        $this->required('descripcion', 'La descripción es obligatoria');
        if (isset($this->data['descripcion'])) {
            $this->maxLength('descripcion', 400, 'La descripción no puede exceder 400 caracteres');
        }

        // Validar moneda (obligatorio, valores permitidos)
        $this->required('moneda', 'La moneda es obligatoria');
        $this->in('moneda', ['COP', 'USD', 'EUR'], 'La moneda debe ser COP, USD o EUR');

        // Validar presupuesto (obligatorio, numérico)
        $this->required('presupuesto', 'El presupuesto es obligatorio');
        $this->numeric('presupuesto', 'El presupuesto debe ser un número');

        // Validar actividad_id (obligatorio)
        $this->required('actividad_id', 'La actividad es obligatoria');

        // Validar fecha_inicio (obligatorio, formato Y-m-d)
        $this->required('fecha_inicio', 'La fecha de inicio es obligatoria');
        $this->dateFormat('fecha_inicio', 'Y-m-d', 'La fecha de inicio debe tener el formato YYYY-MM-DD');

        // Validar hora_inicio (obligatorio, formato H:i)
        $this->required('hora_inicio', 'La hora de inicio es obligatoria');
        $this->timeFormat('hora_inicio', 'H:i', 'La hora de inicio debe tener el formato HH:MM');

        // Validar fecha_cierre (obligatorio, formato Y-m-d)
        $this->required('fecha_cierre', 'La fecha de cierre es obligatoria');
        $this->dateFormat('fecha_cierre', 'Y-m-d', 'La fecha de cierre debe tener el formato YYYY-MM-DD');

        // Validar hora_cierre (obligatorio, formato H:i)
        $this->required('hora_cierre', 'La hora de cierre es obligatoria');
        $this->timeFormat('hora_cierre', 'H:i', 'La hora de cierre debe tener el formato HH:MM');

        // Validar estado (opcional, valores permitidos)
        if (isset($this->data['estado'])) {
            $this->in('estado', ['BORRADOR', 'ACTIVA', 'CERRADA'], 'El estado debe ser BORRADOR, ACTIVA o CERRADA');
        }

        // Validar que fecha_inicio sea anterior a fecha_cierre
        if (isset($this->data['fecha_inicio']) && isset($this->data['fecha_cierre'])) {
            $this->dateBefore('fecha_inicio', 'fecha_cierre');
        }
    }
}
