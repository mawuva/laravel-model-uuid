<?php

namespace Mawuekom\ModelUuid\Utils;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Validate Uuid
 * 
 * Validate incoming UUID before transactions
 * 
 * Trait ValidatesUuid
 * 
 * @package Mawuekom\ModelUuid\Utils
 */
trait ValidatesUuid
{
    /**
     * Check and valid the gien UUID
     *
     * @param string $uuidColumn
     * @param string $uuid
     * @param Illuminate\Database\Eloquent\Model $class
     * @param bool $inTrashed
     *
     * @return void
     */
    private function validatesUuid($uuidColumn, $uuid, $class, $inTrashed = false)
    {
        $this->checkEmpty($uuid, $class);
        $this->checkUuid($uuid, $class);
        $this->checkExists($uuidColumn, $uuid, $class, $inTrashed);
    }

    /**
     * Check if the given UUID is not empty or null
     *
     * @param string $uuid
     * @param string $name
     *
     * @return void
     */
    private function checkEmpty($uuid, $name)
    {
        if (empty($uuid || !isset($uuid) || is_null($uuid))) {
            $this->throwExceptionMessage('Empty', $name);
        }
    }

    /**
     * Check if the given UUID has a valid format
     *
     * @param string $uuid
     * @param string $name
     *
     * @return void
     */
    private function checkUuid($uuid, $name)
    {
        if (!preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $uuid)) {
            $this->throwExceptionMessage('Invalid', $name);
        }
    }

    /**
     * Check if the given Uuid exists
     *
     * @param string $uuidColumn
     * @param string $uuid
     * @param Illuminate\Database\Eloquent\Model $class
     * @param bool $inTrashed
     *
     * @return void
     */
    private function checkExists($uuidColumn, $uuid, $class, $inTrashed = false)
    {
        $check = ($inTrashed)
                    ? $class::where($uuidColumn, $uuid) ->withTrashed() ->first()
                    : $class::where($uuidColumn, $uuid) ->first();

        if (!$check) {
            $this->throwExceptionMessage('Invalid', $class);
        }
    }

    /**
     * Throw exception message
     *
     * @param string $state
     * @param string $className
     *
     * @return void
     */
    private function throwExceptionMessage($state, $className)
    {
        $name = substr($className, strrpos($className, '\\') + 1);

        $name = implode(' ', preg_split('/([[:upper:]][[:lower:]]+)/', $name, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY));

        $this->throwValidationExceptionMessage(sprintf('%s %s ID', $state, $name));
    }

    /**
     * Generate validation exception Message
     *
     * @param sting $message
     *
     * @return void
     */
    private function throwValidationExceptionMessage($message)
    {
        $validator = Validator::make([], []);

        throw new ValidationException($validator, response()->json([
            'error' => $message,
        ], 422));
    }
}