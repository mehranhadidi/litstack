<?php

namespace Fjord\Crud\Models;

use BadMethodCallException;
use Fjord\Crud\Fields\Block\Block;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FormBlock extends FjordFormModel
{
    use ForwardsCalls;

    /**
     * Translation model.
     *
     * @var string
     */
    protected $translationModel = Translations\FormBlockTranslation::class;

    /**
     * No timestamps.
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Fillable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'form_type',
        'config_type',
        'field_id',
        'model_type',
        'model_id',
        'type',
        'content',
        'order_column',
        'value'
    ];

    /**
     * Appends.
     *
     * @var array
     */
    protected $appends = ['fields', 'translation'];

    /**
     * Eager loads.
     *
     * @var array
     */
    protected $with = ['translations', 'media'];

    /**
     * Model relation.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get Blade x component name.
     *
     * @return string|null
     */
    public function getXAttribute()
    {
        return $this->getRepeatable()->getX();
    }

    /**
     * Get view name.
     *
     * @return string|null
     */
    public function getViewAttribute()
    {
        return $this->getRepeatable()->getView();
    }

    /**
     * Get fields from config.
     *
     * @return Field
     */
    public function getFieldsAttribute()
    {
        return $this->getRepeatable()->getRegisteredFields();
    }

    /**
     * Get repeatable.
     *
     * @return Repeatables
     */
    public function getRepeatable()
    {
        $fields = $this->getForm()->getRegisteredFields();

        foreach ($fields as $field) {
            if ($field instanceof Block && $field->id == $this->field_id) {

                // Returning fields from repeatables form.
                return $field->repeatables->{$this->type};
            }
        }
    }

    /**
     * Get an attribute array of all arrayable relations.
     *
     * @return array
     */
    protected function getArrayableRelations()
    {
        $items = $this->getArrayableItems($this->relations);

        // Removing model relation from arrayable items to avoid infinite loop.
        unset($items['model']);

        return $items;
    }

    /**
     * Modified calls.
     * 
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, $params = [])
    {
        try {
            return parent::__call($method, $params);
        } catch (BadMethodCallException $e) {
            return $this->forwardCallTo($this->getRepeatable(), $method, $params);
        }
    }
}
