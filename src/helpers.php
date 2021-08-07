<?php

use Mawuekom\ModelUuid\Utils\ValidatesUuid;

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