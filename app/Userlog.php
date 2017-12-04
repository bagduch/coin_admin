<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Userlog extends Model {

    //
    protected $table = 'userlog';

    public function user() {
        return $this->hasOne("App\User","id","user_id");
    }

}
