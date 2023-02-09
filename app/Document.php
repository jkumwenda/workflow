<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'document_type', 'file_path', 'file_name', 'file_extension', 'checked', 'created_user_id'
    ];
    //
    public function documentable()
    {
        return $this->morphTo();
    }
}
