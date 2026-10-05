<?php

namespace App\Models;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class JsonError
{

    private $error;
    private $message;

    public function __construct(int $error = Response::HTTP_NOT_FOUND, string $message = "Not Found")
    {
        $this->error = $error;
        $this->message[] = $message;
    }

    public function setValidationErrors(ConstraintViolationListInterface $errors)
    {
        foreach ($errors as $error) {
            $this->message[] = "La valeur '" . $error->getInvalidValue() . "' ne respecte pas les règles de validation de la propriété '" . $error->getPropertyPath() . "'";
        }
    }

    /**
     * Get the value of error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Get the value of message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
