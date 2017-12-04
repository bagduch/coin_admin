<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Input;
use App\Role;
use App\User;

class StaffController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    public function index() {


        $title = "Staff Management";
        $roles = Role::all();
        $data = array(
            "title" => $title,
            "roles" => $roles
        );

        return view('staff', $data);
    }

    public function editRole() {
        $id = $_GET['id'];
        $role = Role::find($id);

        $title = "Add Role";
        $data = array(
            "title" => $title,
            "role" => $role,
            "user" => $this->getRoleUser($role->name)
        );
  // echo "<pre>", print_r($data, 1), "</pre>";
    return view('editrole', $data);
    }

    public function editRolePost() {
        $input = Input::all();
        $roleid = $input['id'];
        $this->removeRole($roleid);
        $role = $this->createRolePost($roleid, false);
        if (isset($input['assignrole'])) {
            $userid = array();
            foreach ($input['assignrole'] as $key => $row) {
                $user = User::where('id','=',$key)->first();
                $user->attachRole($role);
            }
        }
       return redirect('staff');
    }

    public function removeRole($id) {
        $input = Input::all();
        if (!isset($id)) {
            $id = $input['id'];
        }
        $role = Role::findOrFail($input['id']); // Pull back a given role
        $role->delete();
    }

    public function getRoleUser($name) {
        $users = User::all();
        foreach ($users as $user) {
            if ($user->hasRole($name)) {
                $user->check = true;
            } else {
                $user->check = false;
            }
        }     
        return $users;
    }

    public function createRole() {

        $title = "Add Role";
        $data = array(
            "title" => $title,
        );

        return view('addrole', $data);
    }

    public function createRolePost($id = null, $redirect = true) {
        $input = Input::all();
        $owner = new Role();
        if (isset($id)) {
            $owner->id = $id;
        }
        $owner->name = $input['name'];
        $owner->display_name = $input['dname']; // optional
        $owner->description = $input['des']; // optional
        if ($owner->save() && $redirect) {
            return redirect('staff');
        } else {
            return $owner;
        }
    }

}
