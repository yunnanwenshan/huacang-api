<?php

namespace App\Models;

use Carbon\Carbon;

class TemplateFormItem extends Model
{
    protected $table = 'template_form_item';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function export()
    {
        return [
            'form_name' => $this->form_name,
            'form_content' => json_decode($this->form_content, true),
            'update_time' => (new Carbon($this->update_time))->format('Y-m-d H:i:s')
        ];
    }
}
