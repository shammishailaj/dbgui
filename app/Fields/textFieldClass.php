<?php

namespace App\Fields;

use App\Fields\fieldClass;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class textFieldClass  extends fieldClass
{
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
                $table->text($code)->nullable();
            });
        }
    }
}


