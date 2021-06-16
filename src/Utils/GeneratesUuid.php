<?php

namespace Mawuekom\ModelUuid\Utils;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Mawuekom\ModelUuid\Utils\ValidatesUuid;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * UUID Generation Trait
 * 
 * Include this trait in any Eloquent model where you want to set  UUID field. 
 * When saving, if the UUID field has not been set, generate a
 * new UUID value, which will be set on the model and saved by Eloquent.
 * 
 * Trait GeneratesUuid
 * 
 * @package Mawuekom\ModelUuid\Utils
 */
trait GeneratesUuid
{
    use ValidatesUuid;

    /**
     * The UUID versions.
     *
     * @var array
     */
    protected $uuidVersions = [
        'uuid1',
        'uuid4',
        'uuid6',
        'ordered',
    ];

    /**
     * Boot the trait, adding a creating observer.
     *
     * When persisting a new model instance, we resolve the UUID field, then set
     * a fresh UUID, taking into account if we need to cast to binary or not.
     *
     * @return void
     */
    public static function bootGeneratesUuid(): void
    {
        static::creating(function ($model) {
            foreach ($model ->uuidColumns() as $item) {
                /* @var \Illuminate\Database\Eloquent\Model|static $model */
                $uuid = $model->resolveUuid();

                if (isset($model->attributes[$item]) && ! is_null($model->attributes[$item])) {
                    /* @var \Ramsey\Uuid\Uuid $uuid */
                    try {
                        $uuid = Uuid::fromString(strtolower($model->attributes[$item]));
                    } 
                    
                    catch (InvalidUuidStringException $e) {
                        $uuid = Uuid::fromBytes($model->attributes[$item]);
                    }
                }

                $model->{$item} = strtolower($uuid->toString());
            }
        });
    }

    /**
     * The name of the column that should be used for the UUID.
     *
     * @return string
     */
    public function uuidColumn(): string
    {
        return 'uuid';
    }

    /**
     * The names of the columns that should be used for the UUID.
     *
     * @return array
     */
    public function uuidColumns(): array
    {
        return [$this->uuidColumn()];
    }

    /**
     * Resolve a UUID instance for the configured version.
     *
     * @return \Ramsey\Uuid\UuidInterface
     */
    public function resolveUuid(): UuidInterface
    {
        return call_user_func([Uuid::class, $this->resolveUuidVersion()]);
    }

    /**
     * Resolve the UUID version to use when setting the UUID value. Default to uuid4.
     *
     * @return string
     */
    public function resolveUuidVersion(): string
    {
        if (property_exists($this, 'uuidVersion') && in_array($this->uuidVersion, $this->uuidVersions)) {
            return $this->uuidVersion === 'ordered' ? 'uuid6' : $this->uuidVersion;
        }

        return 'uuid4';
    }

    /**
     * Scope queries to find by UUID.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|array  $uuid
     * @param  string  $uuidColumn
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereUuid($query, $uuid, $uuidColumn = null): Builder
    {
        $model = get_class();

        $uuidColumn = $this ->checkUuidColumn($uuidColumn);
        $this ->validatesUuid($uuidColumn, $uuid, $model);

        $uuid = array_map(function ($uuid) {
            return Str::lower($uuid);
        }, Arr::wrap($uuid));

        if ($this->isClassCastable($uuidColumn)) {
            $uuid = $this->bytesFromUuid($uuid);
        }

        return $query->whereIn(
            $this->qualifyColumn($uuidColumn),
            Arr::wrap($uuid)
        );
    }

    /**
     * Convert a single UUID or array of UUIDs to bytes.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string  $uuid
     * @return array
     */
    protected function bytesFromUuid($uuid): array
    {
        if (is_array($uuid) || $uuid instanceof Arrayable) {
            array_walk($uuid, function (&$uuid) {
                $uuid = Uuid::fromString($uuid)->getBytes();
            });

            return $uuid;
        }

        return Arr::wrap(Uuid::fromString($uuid)->getBytes());
    }

    /**
     * Check if uuid column exists. 
     * If not return the first column in model's uuid column array
     *
     * @param string $uuidColumn
     *
     * @return string
     */
    private function checkUuidColumn($uuidColumn)
    {
        if (! is_null($uuidColumn) && in_array($uuidColumn, $this->uuidColumns())) {
            return $uuidColumn;
        }

        return $this->uuidColumns()[0];
    }

    /**
     * Get ID from UUID
     *
     * @param string $uuidColumn
     * @param string $uuid
     *
     * @return mixed
     */
    public function getIdFromUuid($uuidColumn, $uuid)
    {
        $model = get_class();

        $uuidColumn = $this ->checkUuidColumn($uuidColumn);
        $this ->validatesUuid($uuidColumn, $uuid, $model);

        $id = property_exists($this, 'primaryKey') ? $this ->primaryKey : 'id';

        return $model::where($uuidColumn, $uuid) ->first() ->{$id};
    }

    /**
     * Load data from the given UUID
     *
     * @param string $uuidColumn
     * @param string $uuid
     *
     * @return mixed
     */
    public function loadFromUuid($uuidColumn, $uuid)
    {
        $model = get_class();

        $uuidColumn = $this ->checkUuidColumn($uuidColumn);
        $this ->validatesUuid($uuidColumn, $uuid, $model);

        $id = property_exists($this, 'primaryKey') ? $this ->primaryKey : 'id';

        return $model::where($id, $this ->getIdFromUuid($uuidColumn, $uuid)) ->first();
    }
}