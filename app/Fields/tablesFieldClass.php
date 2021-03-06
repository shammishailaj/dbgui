<?php

namespace App\Fields;

use App\Fields\fieldClass;

use DB;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use App\Table;

use App\Helpers\Settings;

class tablesFieldClass  extends fieldClass
{
    /**
     * Values  for select
     *
     * @var array
     */
    private $options = [];

    /**
     * Mutate field for adding  form
     *
     * @param  array $field
     * @return array
     */
    public function mutateAddGet ($field)
    {
        //
        if (request()->session()->has('filters.fields.14')){
            $field->default_value = request()->session()->get('filters.fields.14.value');
        }

        return $this->mutateEditGet ($field, null);
    }

    /**
     * Mutate field for list of items
     *
     * @param  string $value
     * @param  array $field
     * @return array
     */
    public function mutateList ($value, $field =  null)
    {
        // Set select's options if they  don't  exist
        if (!isset($this->options[$field->code]))  {
            $this->setOptions($field);
        }

        // Mutate value
        foreach ($this->options[$field->code] as $option) {
            if ($value == $option->id) {
                $value = $option->name;

                return $value;
            }
        }
    }

    /**
     * Get  linked table and options for  select
     *
     * @param  array $field
     * @param  object $itemModel
     * @return array
     */
    public function mutateEditGet ($field, $itemModel)
    {
        // Get  linked table
        $linkedTable = Table::find($field->linked_data_id);

        // get options for select
        if ($linkedTable) {
            if ($linkedTable->code == 'tables') {
                if (Settings::get('dev_mode_tables')) {
                    $field->options = Table::select('id', 'name')->orderBy('weight', 'DESC')->get();
                } else {
                    $field->options = Table::select('id', 'name')->where('flag_system', '<>', 1)->orderBy('weight', 'DESC')->get();
                }
            } else {
                $field->options = DB::table($linkedTable->code)->select('id', 'name')->orderBy('id', 'ASC')->get();
            }
        }

        return $field;
    }

    /**
     * Get  options  for select
     *
     * @param  array $field
     * @param  array $table
     * @return array
     */
    public function setFilterOptions ($field, $table)
    {
        $field->options = [];

        // Set select's options if they  don't  exist
        if (!isset($this->options[$field->code]))  {
            $this->setOptions($field);
        }

        if (isset($this->options[$field->code])) {
            $field->options =  $this->options[$field->code];
        }

        if (request()->session()->has('filters.'.$table->code)) {
            $filters = request()->session()->get('filters.'.$table->code);

            if  (isset($filters[$field->id])) {
                $field->value = $filters [$field->id] ['value'];
            }
        }

        return $field;
    }

    /**
     * Create field/fields in  table
     *
     * @param  array $insertData array  for inserting
     * @param  object $tableModel current table model
     * @return void
     */
    public function createFields ($insertData, $tableModel)
    {
        $code = $insertData ['code'];

        if(Schema::hasColumn($tableModel->code, $code)) {
        }
        else {
            Schema::table($tableModel->code, function (Blueprint $table) use ($code) {
                $table->unsignedBigInteger($code)->nullable();
            });
        }
    }

    /**
     * Create field/fields in  table
     *
     * @param  object $fieldModel
     * @return void
     */
    protected function setOptions ($fieldModel)
    {
        //
        $linkedTable = Table::find($fieldModel->linked_data_id);

        if ($linkedTable) {
            //
             $queryBuiled = DB::table($linkedTable->code);

            if (!Settings::get('dev_mode_tables')) {
                if ($linkedTable->code == 'tables') {
                    $queryBuiled = $queryBuiled->where('flag_system', 0);
                }
            }

            $this->options[$fieldModel->code] = $queryBuiled->orderBy('id', 'ASC')->get();
        }
    }
}
