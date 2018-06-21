<?php

namespace Noking50\Modules\Required\Validation;

use Noking50\Modules\Required\Exceptions\ValidateException;
use Validator;
use Request;

abstract class BaseValidation {

    public function validate_status($request_data = null) {
        $rules = [
            'id' => 'required',
            'status' => 'boolean|required',
        ];
        $attributes = trans('module_required::validation.attributes');

        return $this->validate($rules, $request_data, $attributes);
    }

    public function validate_delete($request_data = null) {
        $rules = [
            'id' => 'required',
        ];
        $attributes = trans('module_required::validation.attributes');

        return $this->validate($rules, $request_data, $attributes);
    }

    /**
     * 
     * @param array $rules
     * @param null|array $request_data
     * @param array $sometimes
     * @return boolean
     */
    protected function validate($rules, $request_data = null, $attributes = [], $messages = [], $sometimes = []) {
        $validator = Validator::make($request_data ?: Request::all(), $rules, $messages, $attributes);
        foreach ($sometimes as $v) {
            $validator->sometimes($v[0], $v[1], $v[2]);
        }
        $validator->setAttributeNames($v);

        if ($validator->fails()) {
            throw new ValidateException($validator->errors());
            return $validator->errors();
        }

        return true;
    }

}
