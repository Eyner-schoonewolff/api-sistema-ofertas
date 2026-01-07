<?php

namespace App\Validators;

use App\Helpers\Request;

/**
 * Clase base para validadores
 */
abstract class Validator
{
    protected array $errors = [];
    protected array $data = [];

    /**
     * Valida los datos
     * 
     * @param array $data
     * @return bool
     */
    public function validate(array $data): bool
    {
        $this->errors = [];
        $this->data = $data;
        $this->rules();
        
        return empty($this->errors);
    }

    /**
     * Define las reglas de validación
     * Debe ser implementado por las clases hijas
     * 
     * @return void
     */
    abstract protected function rules(): void;

    /**
     * Obtiene los errores de validación
     * 
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Agrega un error de validación
     * 
     * @param string $field
     * @param string $message
     * @return void
     */
    protected function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Valida que un campo sea requerido
     * 
     * @param string $field
     * @param string $message
     * @return bool
     */
    protected function required(string $field, string $message = 'Este campo es obligatorio'): bool
    {
        $value = $this->data[$field] ?? null;
        
        if ($value === null || $value === '') {
            $this->addError($field, $message);
            return false;
        }
        
        return true;
    }

    /**
     * Valida la longitud máxima de un string
     * 
     * @param string $field
     * @param int $maxLength
     * @param string $message
     * @return bool
     */
    protected function maxLength(string $field, int $maxLength, ?string $message = null): bool
    {
        $value = $this->data[$field] ?? '';
        
        if (strlen($value) > $maxLength) {
            $message = $message ?? "Este campo no puede exceder {$maxLength} caracteres";
            $this->addError($field, $message);
            return false;
        }
        
        return true;
    }

    /**
     * Valida que un valor esté en una lista permitida
     * 
     * @param string $field
     * @param array $allowedValues
     * @param string $message
     * @return bool
     */
    protected function in(string $field, array $allowedValues, ?string $message = null): bool
    {
        $value = $this->data[$field] ?? null;
        
        if (!in_array($value, $allowedValues, true)) {
            $message = $message ?? "El valor no es válido";
            $this->addError($field, $message);
            return false;
        }
        
        return true;
    }

    /**
     * Valida que un valor sea numérico
     * 
     * @param string $field
     * @param string $message
     * @return bool
     */
    protected function numeric(string $field, ?string $message = null): bool
    {
        $value = $this->data[$field] ?? null;
        
        if (!is_numeric($value)) {
            $message = $message ?? "Este campo debe ser numérico";
            $this->addError($field, $message);
            return false;
        }
        
        return true;
    }

    /**
     * Valida el formato de fecha
     * 
     * @param string $field
     * @param string $format
     * @param string $message
     * @return bool
     */
    protected function dateFormat(string $field, string $format = 'Y-m-d', ?string $message = null): bool
    {
        $value = $this->data[$field] ?? null;
        
        if ($value === null) {
            return true; // Si es null, no validar (usar required si es necesario)
        }
        
        $date = \DateTime::createFromFormat($format, $value);
        
        if (!$date || $date->format($format) !== $value) {
            $message = $message ?? "El formato de fecha no es válido. Debe ser {$format}";
            $this->addError($field, $message);
            return false;
        }
        
        return true;
    }

    /**
     * Valida el formato de hora
     * 
     * @param string $field
     * @param string $format
     * @param string $message
     * @return bool
     */
    protected function timeFormat(string $field, string $format = 'H:i', ?string $message = null): bool
    {
        $value = $this->data[$field] ?? null;
        
        if ($value === null) {
            return true;
        }
        
        $time = \DateTime::createFromFormat($format, $value);
        
        if (!$time || $time->format($format) !== $value) {
            $message = $message ?? "El formato de hora no es válido. Debe ser {$format}";
            $this->addError($field, $message);
            return false;
        }
        
        return true;
    }

    /**
     * Valida que una fecha sea anterior a otra
     * 
     * @param string $field1
     * @param string $field2
     * @param string $message
     * @return bool
     */
    protected function dateBefore(string $field1, string $field2, ?string $message = null): bool
    {
        $date1 = $this->data[$field1] ?? null;
        $date2 = $this->data[$field2] ?? null;
        $time1 = $this->data[str_replace('fecha', 'hora', $field1)] ?? '00:00';
        $time2 = $this->data[str_replace('fecha', 'hora', $field2)] ?? '00:00';
        
        if ($date1 === null || $date2 === null) {
            return true; // Dejar que required valide esto
        }
        
        $datetime1 = \DateTime::createFromFormat('Y-m-d H:i', "{$date1} {$time1}");
        $datetime2 = \DateTime::createFromFormat('Y-m-d H:i', "{$date2} {$time2}");
        
        if (!$datetime1 || !$datetime2) {
            return true; // Dejar que dateFormat valide esto
        }
        
        if ($datetime1 >= $datetime2) {
            $message = $message ?? "La fecha y hora de inicio deben ser menores a la fecha y hora de cierre";
            $this->addError($field1, $message);
            return false;
        }
        
        return true;
    }
}
