<?php

namespace Noking50\Modules\Required\Models;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Pagination;

abstract class BaseModel extends Model {
    ## Scope

    /**
     * 取得資料總數並設定分頁
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSetPagination($query) {
        $total_count = $query->cloneWithout(['columns', 'orders', 'limit', 'offset'])
                ->cloneWithoutBindings(['select', 'order'])
                ->count();
        $paging['total'] = $total_count;
        Pagination::setPagination($paging);

        return $query->skip(Pagination::getItemSkip())->take(Pagination::getItemTake());
    }

    /**
     * 設定排序
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSetSort($query, $sorts = null) {
        if (!is_array($sorts) || count($sorts) <= 0) {
            $sorts = [
                [$this->table . '.' . $this->primaryKey, 'desc']
            ];
        } else if (!is_array(head($sorts))) {
            $sorts = [$sorts];
        }

        foreach ($sorts as $v) {
            $query->orderBy($v[0], $v[1]);
        }

        return $query;
    }

    /**
     * 選取後台新增人員
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSelectCreatorAdmin($query) {
        $user_table = config('user.group.admin.datatable', '');
        if (Schema::hasTable($user_table)) {
            $query->addSelect([
                        "{$this->table}.create_{$user_table}_id",
                        "create_{$user_table}.name AS create_{$user_table}_name",
                    ])
                    ->leftJoin("{$user_table} AS create_{$user_table}", $this->table . ".create_{$user_table}_id", '=', "create_{$user_table}.id");
        } else {
            $query->addSelect([
                "{$this->table}.create_{$user_table}_id'",
                DB::raw(" NULL AS create_{$user_table}_name "),
            ]);
        }
        return $query;
    }

    /**
     * 選取後台更新人員
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSelectUpdaterAdmin($query) {
        $user_table = config('user.group.admin.datatable', '');
        if (Schema::hasTable($user_table)) {
            $query->addSelect([
                        "{$this->table}.update_{$user_table}_id",
                        "update_{$user_table}.name AS update_{$user_table}_name",
                    ])
                    ->leftJoin("{$user_table} AS update_{$user_table}", $this->table . ".update_{$user_table}_id", '=', "update_{$user_table}.id");
        } else {
            $query->addSelect([
                "{$this->table}.update_{$user_table}_id",
                DB::raw(" NULL AS update_{$user_table}_name "),
            ]);
        }

        return $query;
    }

    /**
     * 
     * @param type $query
     * @return type
     */
    public function scopeActive($query) {
        return $query->where($this->table . '.status', '=', 1);
    }

    /**
     * 
     * @param type $query
     * @param type $columns
     * @param type $lang
     * @param type $replace
     * @return type
     */
    public function scopeTranslate($query, $columns, $lang = null, $replace = true) {
        return $this->scopeTranslateTable($query, $columns, $this->table, $this->primaryKey, null, $lang, $replace);
    }

    /**
     * 
     * @param type $query
     * @param type $columns
     * @param type $table
     * @param type $table_pk
     * @param type $table_alias
     * @param type $lang
     * @param type $replace
     * @return type
     */
    public function scopeTranslateTable($query, $columns, $table, $table_pk, $table_alias = null, $lang = null, $replace = true) {
        $columns = array_wrap($columns);
        $lang_default = config('app.fallback_locale');
        $lang = $lang ?: config('app.locale');
        $table_join = $table_alias ?: $table;
        $table_lang_org = $table . '_lang';
        $table_lang = $table_alias ? $table_alias . '_lang' : $table_lang_org;
        $table_lang_default = $table_lang . '_default';

        // column
        if ($lang == $lang_default || $replace === false) {
            if ($table_join == $this->table) {
                foreach ($columns as $k => $v) {
                    $columns[$k] = "{$table_lang}.{$v}";
                }
            } else {
                foreach ($columns as $k => $v) {
                    $columns[$k] = "{$table_lang}.{$v} AS {$table_join}_{$v}";
                }
            }
        } else {
            if ($table_join == $this->table) {
                foreach ($columns as $k => $v) {
                    $columns[$k] = \DB::raw("IFNULL(`{$table_lang}`.`{$v}`, `{$table_lang_default}`.`{$v}`) AS {$v}");
                }
            } else {
                foreach ($columns as $k => $v) {
                    $columns[$k] = \DB::raw("IFNULL(`{$table_lang}`.`{$v}`, `{$table_lang_default}`.`{$v}`) AS {$table_join}_{$v}");
                }
            }
        }
        $query->addSelect($columns);

        // join
        $query->leftJoin($table_alias ? "{$table_lang_org} AS {$table_lang}" : $table_lang, function ($join) use($table, $table_pk, $table_join, $table_lang, $lang) {
            $join->on("{$table_join}.{$table_pk}", '=', "{$table_lang}.{$table}_{$table_pk}");
            $join->where("{$table_lang}.lang", '=', $lang);
        });
        if ($lang != $lang_default && $replace === true) {
            $query->leftJoin("{$table_lang_org} AS {$table_lang_default}", function ($join) use($table, $table_pk, $table_join, $table_lang_default, $lang_default) {
                $join->on("{$table_join}.{$table_pk}", '=', "{$table_lang_default}.{$table}_{$table_pk}");
                $join->where("{$table_lang_default}.lang", '=', $lang_default);
            });
        }

        return $query;
    }

    # Accessor

    public function getCreateMemberAdminNameAttribute($value) {
        $user_table = config('user.group.admin.datatable');
        if (!is_null($this->{"create_{$user_table}_id"}) && $this->{"create_{$user_table}_id"} == 0) {
            return config('user.group.admin.super.name');
        }

        return $value;
    }

    public function getUpdateMemberAdminNameAttribute($value) {
        $user_table = config('user.group.admin.datatable');
        if (!is_null($this->{"update_{$user_table}_id"}) && $this->{"update_{$user_table}_id"} == 0) {
            return config('user.group.admin.super.name');
        }

        return $value;
    }

}
