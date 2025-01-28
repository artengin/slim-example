<?php

namespace App;

class Validator implements ValidatorInterface
{
    public function validate(array $users)
    {
        $errors = [];
        if (empty($users['name'])) {
            $errors['name'] = "Can't be blank";
        }

        if (empty($users['email'])) {
            $errors['email'] = "Can't be blank";
        }

        return $errors;
    }
}
