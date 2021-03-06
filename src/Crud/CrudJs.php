<?php

namespace Ignite\Crud;

use Carbon\CarbonInterface;
use Ignite\Crud\Models\LitFormModel;
use Ignite\Support\VueProp;

class CrudJs extends VueProp
{
    /**
     * Model.
     *
     * @var mixed
     */
    protected $model;

    /**
     * Create new CrudJs instance.
     *
     * @param mixed $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Get array.
     *
     * @return array
     */
    public function render(): array
    {
        return [
            'attributes'   => $this->getModelAttributes(),
            'translatable' => is_translatable($this->model),
            'cast'         => $this->usesJsoncastValue(),
        ];
    }

    /**
     * Model to array.
     *
     * @return array
     */
    public function getModelAttributes()
    {
        $array = $this->castAttributes(
            $this->model->toArray()
        );

        if (! array_key_exists('translations', $array)) {
            return $array;
        }

        // Setting translation attributes that are used for updating the model.
        // (de, en, ...)
        $translationsArray = $this->castAttributes(
            $this->model->getTranslationsArray()
        );

        $array = array_merge($translationsArray, $array);

        return $array;
    }

    /**
     * Cast attributes for Fields.
     *
     * @param  array $array
     * @return array
     */
    public function castAttributes(array $array)
    {
        foreach ($array as $key => $value) {
            if ($value instanceof CarbonInterface) {
                $array[$key] = $value->toDateTimeString();
            }
        }

        return $array;
    }

    /**
     * Is the Crud Model casting the field values into one json column.
     *
     * @return bool
     */
    public function usesJsoncastValue()
    {
        return $this->model instanceof LitFormModel;
    }
}
