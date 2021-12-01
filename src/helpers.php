<?php

use Mawuekom\ModelUuid\Utils\ValidatesUuid;
use Illuminate\Database\Eloquent\Model;

if (!function_exists('is_the_given_id_a_uuid')) {
    /**
     * Check if the givent id is a uuid
     *
     * @param string $uuidColumn
     * @param string $uuid
     * @param Illuminate\Database\Eloquent\Model $class
     * @param bool $inTrashed
     *
     * @return bool
     */
    function is_the_given_id_a_uuid($uuidColumn, $uuid, $class, $inTrashed = false): bool {
        $data = (new class { 
            use ValidatesUuid;

            /**
             * Resolve Uuid
             *
             * @param string $uuidColumn
             * @param string $uuid
             * @param Illuminate\Database\Eloquent\Model $class
             *
             * @return bool
             */
            public function resolveUuid($uuidColumn, $uuid, $class, $inTrashed = false): bool {
                try {
                    $this ->validatesUuid($uuidColumn, $uuid, $class, $inTrashed);
                    return true;
                }

                catch (Exception $e) {
                    //$msg = $e ->getMessage();
                    return false;
                }
            }
        });

        return $data ->resolveUuid($uuidColumn, $uuid, $class, $inTrashed);
    }
}

if (!function_exists('resolve_key')) {
    /**
     * Get key to use to make queries
     * 
     * @param string|Illuminate\Database\Eloquent\Model $model
     * @param int|string $id
     * @param bool $inTrashed
     * 
     * @return string|null
     */
    function resolve_key($model, $id = null, $inTrashed = false) {
        $model          = (!$model instanceof Model) ? app($model) : $model;
        $uuidColumn     = $model ->checkUuidColumn();
        $modelPK        = $model ->getKeyName();

        return (is_the_given_id_a_uuid($uuidColumn, $id, $model, $inTrashed))
                    ? $uuidColumn
                    : $modelPK;
    }
}