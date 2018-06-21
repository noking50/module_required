<?php

namespace Noking50\Modules\Required\Exceptions;

use Exception;

class ValidateException extends Exception {

    /**
     *
     * @var Illuminate\Support\MessageBag
     */
    protected $validation;

    /**
     * 
     * @param Illuminate\Support\MessageBag $validation
     */
    public function __construct($validation) {
        parent::__construct(trans('message.error.validation'));
        $this->validation = $validation;
    }
    
    /**
     * 
     * @return Illuminate\Support\MessageBag
     */
    public function getValidation() {
        return $this->validation;
    }

}
