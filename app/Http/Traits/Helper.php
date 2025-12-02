<?php

namespace App\Http\Traits;

use App\Constants\Constants;
use App\Models\RolePermission;
use App\Models\TreeEntity;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

trait Helper
{
    protected function bind_to_template($replacements, $template)
    {
        return preg_replace_callback('/{{(.+?)}}/', function ($matches) use ($replacements) {
            return array_key_exists($matches[1], $replacements) ? $replacements[$matches[1]] : null;
        }, $template);
    }
    protected function pick($table, $field, $cond, $val)
    {
        $tt = null;
        if ($cond == null) {
            $query = DB::table($table)
                ->select(DB::raw($field . ' AS name'));
        } else {
            $query = DB::table($table)
                ->select(DB::raw($field . ' AS name'));
            $query->where($cond, $val);
        }
        $data = $query->get();
        foreach ($data as $index => $da) {
            if ($tt == null) {
                $tt = $da->name;
            } else {
                $tt = $tt . '<BR>' . $da->name;
            }
        }
        return $tt;
    }

    protected function parseJsonArray($jsonArray, $pid = 0)
    {
        $return = array();
        foreach ($jsonArray as $subArray) {
            $returnSubSubArray = array();
            if (isset($subArray['menus'])) {
                $returnSubSubArray = $this->parseJsonArray($subArray['menus'], $subArray['id']);
            }
            $return[] = array('id' => $subArray['id'], 'pid' => $pid);
            $return = array_merge($return, $returnSubSubArray);
        }
        return $return;
    }

    protected function recursiveDelete($id, $status)
    {
        $query = TreeEntity::where('pid', '=', $id)->get();
        if ($query->count() > 0) {
            foreach ($query as $key => $value) {
                $this->recursiveDelete($value->id, $status);
            }
        }
        $treeentry = TreeEntity::find($id);
        if ($treeentry == null) {
            return false;
        }
        $treeentry['status'] = $status;
        $treeentry->save();
        return true;
    }
    protected function recursivHeadereDelete($id, $status)
    {
        $query = TreeEntity::where('pid', '=', $id)->get();
        if ($query->count() > 0) {
            foreach ($query as $key => $value) {
                $this->recursivHeadereDelete($value->id, $status);
            }
        }
        $treeentry = TreeEntity::find($id);
        if ($treeentry == null) {
            return false;
        }
        $treeentry['status'] = $status;
        $treeentry->save();
        return true;
    }

    protected function hasrolePermition($request, $type)
    {
        $currenturl = $request->segment(2);
        $role = Auth::user()->user_type;
        $user_role = RolePermission::where('role_id', $role)->first();
        $permission = (object)[];
        if ($currenturl != '' && $user_role) {
            $permission = DB::table('tree_entities')
                ->select('node_name', 'role_permissions.view', 'role_permissions.add', 'role_permissions.edit', 'role_permissions.edit', 'role_permissions.edit_other', 'role_permissions.delete', 'role_permissions.delete_other')
                ->join('role_permissions', 'role_permissions.view', '=', 'tree_entities.id')
                ->where('tree_entities.route_name', 'Like', '%' . $currenturl)
                ->where('role_permissions.role_id', $user_role->role_id)
                ->first();

            if ($permission) {
                $permission->status = 'true';
                $haspermission = json_decode(json_encode($permission), true);
                switch ($type) {
                    case "view":
                        if ($haspermission['view'] > 0) {
                            return  $permission;
                        } else {
                            return $permission->status = 'false';
                        }
                        break;
                    case "add":
                        if ($haspermission['add'] > 0) {
                            return  $permission;
                        } else {
                            return $permission->status = 'false';
                        }
                        break;
                    case "show":
                        if ($haspermission['edit'] > 0) {
                            return  $permission;
                        } else {
                            return $permission->status = 'false';
                        }
                        break;
                    case "edit":
                        if ($haspermission['edit'] > 0) {
                            return  $permission;
                        } else {
                            return $permission->status = 'false';
                        }
                        break;
                    case "delete":
                        if ($haspermission['delete'] > 0) {
                            return  $permission;
                        } else {
                            return $permission->status = 'false';
                        }
                        break;
                    default:
                        return $permission->status = 'false';
                }
            } else {
                $permission = (object)[];
                $permission->status = 'false';
                return $permission;
            }
        } else {
            return $permission->status = 'false';
        }
    }

    protected function is_base64($s)
    {
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s)) {
            return false;
        }
        $decoded = base64_decode($s, true);
        if (false === $decoded) {
            return false;
        }
        if (base64_encode($decoded) != $s) {
            return false;
        }
        return true;
    }


    /**
     * array_flatten
     *
     * @param  mixed  $array
     * @param  mixed  $keyval
     * @return mixed $array
     */
    protected function array_flatten($array, $keyval = null)
    {
        if (! is_array($array)) {
            return false;
        }
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {

                $result = array_merge($result, $this->array_flatten($value, $key));
            } else {
                if ($keyval != '') {
                    $result[$keyval . '.' . $key] = $value;
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    protected function flatten(array $array)
    {
        if (! is_array($array)) {
            // nothing to do if it's not an array
            return [$array];
        }

        $result = [];
        foreach ($array as $value) {
            // explode the sub-array, and add the parts
            $result = array_merge($result, $this->flatten($value));
        }

        return $result;
    }
    /**
     * array_values_recursive
     *
     * @param  mixed  $array
     * @return void
     */
    protected function array_values_recursive($array)
    {
        $temp = [];
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $temp[] = is_array($value) ? $this->array_values_recursive($value) : $value;
            } else {
                $temp[$key] = is_array($value) ? $this->array_values_recursive($value) : $value;
            }
        }

        return $temp;
    }
}
